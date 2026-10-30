<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Buffet;
use App\Repository\Models\Restaurante\BuffetCierre;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Eloquent\Collection;

final class BuffetRepositorio
{
    /** @param array<string, mixed> $data */
    public function crear(array $data): Buffet
    {
        return Buffet::query()->create($data);
    }

    public function monedaPredeterminadaId(): ?int
    {
        $id = Moneda::query()->where('es_predeterminada', true)->value('id');

        return is_numeric($id) ? (int) $id : null;
    }

    public function bloquear(int $id): Buffet
    {
        return Buffet::query()->lockForUpdate()->findOrFail($id);
    }

    public function stockOrigen(int $id): Stock
    {
        return Stock::query()->with('lote')->lockForUpdate()->findOrFail($id);
    }

    /** @return Collection<int, Stock> */
    public function stocks(Buffet $buffet): Collection
    {
        return $buffet->existencias()->where('cantidad_actual', '>', 0)->with('lote')->orderBy('id')->lockForUpdate()->get();
    }

    public function recibir(Buffet $buffet, Stock $origen, float $cantidad): void
    {
        $destino = $buffet->existencias()->where('producto_id', $origen->producto_id ?? $origen->variante?->producto_id)->where('producto_variante_id', $origen->producto_variante_id)->where('lote_id', $origen->lote_id)->lockForUpdate()->first();
        if ($destino === null) {
            $destino = new Stock(['stockable_type' => Buffet::class, 'stockable_id' => $buffet->id, 'producto_id' => $origen->producto_id ?? $origen->variante?->producto_id, 'producto_variante_id' => $origen->producto_variante_id, 'lote_id' => $origen->lote_id, 'cantidad_actual' => 0, 'cantidad_ideal' => 0]);
        }
        $destino->cantidad_actual = round((float) $destino->cantidad_actual + $cantidad, 4);
        $destino->save();
    }

    /** @param array<string, mixed> $data */
    public function actualizar(Buffet $buffet, array $data): Buffet
    {
        $buffet->update($data);

        return $buffet->refresh();
    }

    /** @param array<string, mixed> $data */
    public function cierre(array $data): void
    {
        BuffetCierre::query()->create($data);
    }

    public function cuenta(int $id): Cuenta
    {
        return Cuenta::query()->lockForUpdate()->findOrFail($id);
    }

    public function plato(int $id): ?Plato
    {
        return Plato::query()->with('ingredientes')->find($id);
    }

    public function variante(int $id): ?ProductoVariante
    {
        return ProductoVariante::query()->find($id);
    }

    /** @param list<array{plato_id: int, producto_variante_id: int, porciones_previstas: int}> $menu */
    public function guardarMenu(Buffet $buffet, string $servicio, array $menu): Buffet
    {
        $buffet->menu()->delete();
        foreach ($menu as $fila) {
            $buffet->menu()->create($fila);
        }

        return $this->actualizar($buffet, ['tipo_servicio' => $servicio]);
    }

    public function admiteVariante(Buffet $buffet, ?int $varianteId): bool
    {
        return ! $buffet->menu()->exists() || $buffet->menu()->where('producto_variante_id', $varianteId)->exists();
    }
}
