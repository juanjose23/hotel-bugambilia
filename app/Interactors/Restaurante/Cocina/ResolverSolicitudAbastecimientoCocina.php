<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Compras\EstadoSolicitud;
use App\Enums\Restaurante\UbicacionCocina;
use App\Notifications\Compras\NotificadorCompras;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

final readonly class ResolverSolicitudAbastecimientoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private NotificadorCompras $notificadorCompras,
    ) {}

    public function ejecutar(Solicitud $solicitud, ?int $usuarioId = null): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $usuarioId): Solicitud {
            $solicitud->loadMissing(['items.producto', 'items.variante.producto']);

            if ($solicitud->estado !== EstadoSolicitud::Aprobada) {
                throw new DomainException('Solo se puede resolver inventario de una solicitud aprobada.');
            }

            $this->validarAutorizacionInventario($usuarioId);

            $destinoId = $this->obtenerUbicacionCocinaId();
            $faltantes = $this->faltantesInventarioInterno($solicitud, $destinoId);

            if ($faltantes !== []) {
                $this->notificadorCompras->abastecimientoCocinaRequiereCompra($solicitud, $faltantes);

                throw new DomainException(
                    'Inventario interno insuficiente. Genere cotización / orden de compra para: '.implode('; ', $faltantes).'.'
                );
            }

            $traslados = [];

            foreach ($solicitud->items as $item) {
                $traslados = [
                    ...$traslados,
                    ...$this->resolverItemDesdeBodegas($solicitud, $item, $destinoId, $usuarioId),
                ];
            }

            $destino = Ubicacion::query()->find($destinoId);
            $detalle = implode('; ', array_slice($traslados, 0, 8));
            $nota = '['.now()->format('d/m/Y H:i').'] RESUELTO CON INVENTARIO: '.$detalle;
            $solicitud->notas = trim((string) ($solicitud->notas ?? '')."\n".$nota);
            $solicitud->save();

            $this->notificadorCompras->abastecimientoCocinaResueltoConInventario($solicitud, $traslados);

            return $solicitud->refresh();
        });
    }

    private function validarAutorizacionInventario(?int $usuarioId): void
    {
        $usuario = $usuarioId !== null ? User::query()->find($usuarioId) : null;

        if (! $usuario instanceof User || ! $usuario->can('Inventario:ResolverAbastecimientoCocina')) {
            throw new DomainException('Debe autorizar esta resolución un usuario con permiso de inventario.');
        }
    }

    /**
     * @return list<string>
     */
    private function faltantesInventarioInterno(Solicitud $solicitud, int $destinoId): array
    {
        $faltantes = [];

        foreach ($solicitud->items as $item) {
            $varianteId = (int) ($item->producto_variante_id ?? 0);
            $cantidad = $this->cantidadAResolver($item);

            if ($varianteId <= 0 || $cantidad <= 0) {
                continue;
            }

            $sum = $this->stocksDisponibles($varianteId, $destinoId)->sum('cantidad_actual');
            $disponible = is_numeric($sum) ? (float) $sum : 0.0;

            if ($disponible < $cantidad) {
                $faltantes[] = sprintf(
                    '%s disponible %s / requerido %s',
                    $this->nombreItem($item),
                    number_format($disponible, 2),
                    number_format($cantidad, 2),
                );
            }
        }

        return $faltantes;
    }

    /**
     * @return list<string>
     */
    private function resolverItemDesdeBodegas(Solicitud $solicitud, SolicitudItem $item, int $destinoId, ?int $usuarioId): array
    {
        $varianteId = (int) ($item->producto_variante_id ?? 0);
        $productoId = (int) ($item->producto_id ?? 0);
        $pendiente = $this->cantidadAResolver($item);
        $traslados = [];

        if ($varianteId <= 0 || $productoId <= 0 || $pendiente <= 0) {
            return [];
        }

        $destino = Ubicacion::query()->find($destinoId);
        $destinoNombre = $destino->nombre ?? 'Cocina';

        foreach ($this->stocksDisponibles($varianteId, $destinoId) as $stockOrigen) {
            if ($pendiente <= 0) {
                break;
            }

            $cantidad = min($pendiente, (float) $stockOrigen->cantidad_actual);

            if ($cantidad <= 0) {
                continue;
            }

            $origenId = (int) $stockOrigen->stockable_id;
            $origen = Ubicacion::query()->find($origenId);
            $origenNombre = $origen->nombre ?? "Bodega #{$origenId}";
            $stockOrigen->cantidad_actual = (float) $stockOrigen->cantidad_actual - $cantidad;
            $this->repositorio->guardarStock($stockOrigen);

            $stockDestino = $this->repositorio->obtenerStockPorVariante($destinoId, $varianteId);
            if (! $stockDestino instanceof Stock) {
                $stockDestino = Stock::query()->create([
                    'stockable_type' => Ubicacion::class,
                    'stockable_id' => $destinoId,
                    'producto_variante_id' => $varianteId,
                    'lote_id' => $stockOrigen->lote_id,
                    'cantidad_ideal' => 0,
                    'cantidad_actual' => 0,
                ]);
            }

            $stockDestino->cantidad_actual = (float) $stockDestino->cantidad_actual + $cantidad;
            $this->repositorio->guardarStock($stockDestino);

            $costoUnitario = $stockOrigen->lote?->costo_unitario;
            $this->repositorio->registrarMovimiento([
                'tipo' => 'TRASLADO',
                'lote_id' => $stockOrigen->lote_id,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario !== null ? (float) $costoUnitario * $cantidad : null,
                'ubicacion_origen_id' => $origenId,
                'ubicacion_destino_id' => $destinoId,
                'documento_tipo' => 'solicitud_abastecimiento_cocina',
                'documento_id' => $solicitud->id,
                'referencia' => "Resolución automática de solicitud {$solicitud->codigo} desde inventario interno",
                'creado_por_id' => $usuarioId,
            ]);

            $pendiente -= $cantidad;
            $traslados[] = sprintf(
                '%s -> %s: %s x %s',
                $origenNombre,
                $destinoNombre,
                $this->nombreItem($item),
                number_format($cantidad, 2),
            );
        }

        return $traslados;
    }

    /**
     * @return EloquentCollection<int, Stock>
     */
    private function stocksDisponibles(int $varianteId, int $destinoId): EloquentCollection
    {
        /** @var EloquentCollection<int, Stock> $stocks */
        $stocks = Stock::query()
            ->with('lote')
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', '!=', $destinoId)
            ->where('producto_variante_id', $varianteId)
            ->where('cantidad_actual', '>', 0)
            ->orderByDesc('cantidad_actual')
            ->lockForUpdate()
            ->get();

        return $stocks;
    }

    private function cantidadAResolver(SolicitudItem $item): float
    {
        $aprobada = (float) ($item->cantidad_aprobada ?? 0);

        return $aprobada > 0 ? $aprobada : (float) $item->cantidad_solicitada;
    }

    private function nombreItem(SolicitudItem $item): string
    {
        $producto = $item->variante !== null ? ($item->variante->producto->nombre ?? 'Producto') : ($item->producto->nombre ?? 'Producto');
        $variante = $item->variante !== null ? ($item->variante->nombre_variante ?: $item->variante->codigo) : null;

        return $variante !== null ? "{$producto} - {$variante}" : $producto;
    }

    private function obtenerUbicacionCocinaId(): int
    {
        $id = Ubicacion::query()
            ->where('nombre', UbicacionCocina::RESTAURANTE->value)
            ->orWhere('nombre', 'Cocina')
            ->orWhere('nombre', 'like', '%Cocina%')
            ->orWhere('tipo', 'cocina')
            ->value('id');

        if (! is_numeric($id)) {
            throw new DomainException('No existe una ubicación de cocina configurada para recibir abastecimiento.');
        }

        return (int) $id;
    }
}
