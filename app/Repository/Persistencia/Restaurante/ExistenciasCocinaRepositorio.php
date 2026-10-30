<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Enums\Inventario\EstadoLote;
use App\Enums\Restaurante\AreaCocina;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Shared\Stock;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ExistenciasCocinaRepositorio
{
    /** @return Builder<Stock> */
    public function disponibles(bool $permitirNoDisponibles = false): Builder
    {
        $query = Stock::query()->where('cantidad_actual', '>', 0);
        if (! $permitirNoDisponibles) {
            $query->where(fn (Builder $q) => $q->whereNull('lote_id')->orWhereHas('lote', fn (Builder $l) => $l
                ->where('estado', EstadoLote::Disponible->value)
                ->where(fn (Builder $d) => $d->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', now()->toDateString()))));
        }

        return $query;
    }

    /** @return Collection<int, Stock> */
    public function porVariante(int $varianteId, int $ubicacionId, bool $bloquear = false): Collection
    {
        return $this->obtener($this->disponibles()->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $ubicacionId)->where('producto_variante_id', $varianteId), $bloquear);
    }

    /** @return Builder<Stock> */
    public function producto(int $productoId, ?int $varianteId, ?int $mesaId): Builder
    {
        $query = $this->disponibles();
        if ($varianteId !== null && $varianteId > 0) {
            $query->where('producto_variante_id', $varianteId)->whereHas('variante', fn (Builder $v) => $v->where('producto_id', $productoId));
        } else {
            $query->where('producto_id', $productoId)->whereNull('producto_variante_id');
        }
        $mesa = $mesaId !== null ? Espacio::query()->find($mesaId) : null;
        if ($mesaId !== null && ! $mesa instanceof Espacio) {
            throw new DomainException('Punto de venta no encontrado.');
        }
        if ($mesa instanceof Espacio) {
            $espacios = array_values(array_filter([$mesa->id, $mesa->padre_id]));
            $ubicaciones = array_values(array_filter([$mesa->getAttribute('ubicacion_cocina_id'), $mesa->getAttribute('ubicacion_bar_id'), $mesa->padre?->getAttribute('ubicacion_cocina_id'), $mesa->padre?->getAttribute('ubicacion_bar_id')], 'is_numeric'));
            $query->where(fn (Builder $q) => $q
                ->where(fn (Builder $s) => $s->where('stockable_type', Espacio::class)->whereIn('stockable_id', $espacios))
                ->orWhere(fn (Builder $u) => $u->where('stockable_type', Ubicacion::class)->whereIn('stockable_id', $ubicaciones)));
        } else {
            $query->where('stockable_type', Ubicacion::class)->whereIn('stockable_id', Ubicacion::query()->select('id')
                ->where('estado', 1)->where(fn (Builder $u) => $u->whereIn('tipo', ['cocina', 'bar'])
                ->orWhere('nombre', 'like', '%Cocina%')->orWhere('nombre', 'like', '%Bar%')));
        }

        return $query;
    }

    /** @param Builder<Stock> $query
     * @return Collection<int, Stock>
     */
    public function obtener(Builder $query, bool $bloquear = false): Collection
    {
        $query->orderByRaw('(SELECT fecha_vencimiento FROM inv_lotes WHERE inv_lotes.id = stocks.lote_id) ASC NULLS LAST')->orderBy('stocks.id');
        if ($bloquear) {
            $query->lockForUpdate();
        }

        return $query->with(['lote', 'variante'])->get();
    }

    public function ubicacion(?AreaCocina $area = null, ?int $mesaId = null): int
    {
        if ($mesaId !== null) {
            $mesa = Espacio::query()->with('padre')->findOrFail($mesaId);
            $campo = $area === AreaCocina::BAR ? 'ubicacion_bar_id' : 'ubicacion_cocina_id';
            $configurada = $mesa->getAttribute($campo) ?? $mesa->padre?->getAttribute($campo);
            if (is_numeric($configurada)) {
                $activa = Ubicacion::query()->whereKey((int) $configurada)->where('estado', 1)->exists();
                if (! $activa) {
                    throw new DomainException('La ubicación configurada para el punto de venta no está activa.');
                }

                return (int) $configurada;
            }
        }
        $query = Ubicacion::query()->where('estado', 1);
        if ($area === AreaCocina::BAR) {
            $query->where(fn (Builder $q) => $q->where('tipo', 'bar')->orWhere('nombre', 'like', '%Bar%'));
        } else {
            $query->where(fn (Builder $q) => $q->where('nombre', UbicacionCocina::RESTAURANTE->value)->orWhere('nombre', 'Cocina')->orWhere('tipo', 'cocina'));
        }
        $id = $query->orderBy('id')->value('id');
        if (! is_numeric($id)) {
            throw new DomainException('Debe configurar una ubicación para el área de preparación.');
        }

        return (int) $id;
    }

    public function loteBloqueado(?int $id): ?Lote
    {
        return $id !== null ? Lote::query()->lockForUpdate()->find($id) : null;
    }

    /** @param \Illuminate\Support\Collection<int, Stock> $stocks */
    public function cantidad(\Illuminate\Support\Collection $stocks): float
    {
        $cantidad = 0.0;
        foreach ($stocks as $stock) {
            $cantidad += (float) $stock->cantidad_actual;
        }

        return $cantidad;
    }

    /** @param \Illuminate\Support\Collection<int, Stock> $stocks
     * @return Collection<int, Stock>
     */
    public function bloquearSeleccion(\Illuminate\Support\Collection $stocks): Collection
    {
        $ids = $stocks->map(fn (Stock $stock): int => $stock->id)->all();

        return $this->obtener(Stock::query()->whereKey($ids), true);
    }

    public function tieneConsumo(int $itemId): bool
    {
        return MovimientoStock::query()->where('documento_tipo', 'pedido_item')->where('documento_id', $itemId)->where('tipo', 'CONSUMO')->exists();
    }

    public function guardar(Stock $stock): void
    {
        $stock->save();
    }

    public function guardarLote(Lote $lote): void
    {
        $lote->save();
    }

    /** @param array<string, mixed> $datos */
    public function movimiento(array $datos): void
    {
        MovimientoStock::query()->create($datos);
    }

    /** @return Collection<int, Stock> */
    public function productoEnUbicacion(int $productoId, int $ubicacionId, bool $bloquear = false, bool $permitirNoDisponibles = false): Collection
    {
        return $this->obtener($this->disponibles($permitirNoDisponibles)->where('stockable_type', Ubicacion::class)->where('stockable_id', $ubicacionId)
            ->where(fn (Builder $q) => $q->where('producto_id', $productoId)->orWhereHas('variante', fn (Builder $v) => $v->where('producto_id', $productoId))), $bloquear);
    }

    /** @param list<int> $ids */
    public function bloquearUbicaciones(array $ids): void
    {
        $ubicaciones = Ubicacion::query()->whereIn('id', array_unique($ids))->orderBy('id')->lockForUpdate()->get();
        if ($ubicaciones->count() !== count(array_unique($ids))) {
            throw new DomainException('Ubicación no encontrada.');
        }
    }

    public function recibir(Stock $origen, int $destinoId, float $cantidad): Stock
    {
        $destino = Stock::query()->where('stockable_type', Ubicacion::class)->where('stockable_id', $destinoId)
            ->where('producto_variante_id', $origen->producto_variante_id)->where('lote_id', $origen->lote_id)
            ->when($origen->producto_variante_id === null, fn (Builder $q) => $q->where('producto_id', $origen->producto_id))
            ->lockForUpdate()->first();
        if ($destino === null) {
            $destino = new Stock(['stockable_type' => Ubicacion::class, 'stockable_id' => $destinoId, 'producto_id' => $origen->producto_id ?? $origen->variante?->producto_id, 'producto_variante_id' => $origen->producto_variante_id, 'lote_id' => $origen->lote_id, 'cantidad_ideal' => 0, 'cantidad_actual' => 0]);
        }
        $destino->cantidad_actual = round((float) $destino->cantidad_actual + $cantidad, 4);
        $destino->save();

        return $destino;
    }
}
