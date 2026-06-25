<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Shared\Stock as SharedStock;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use Illuminate\Support\Facades\DB;

class ReabastecerUbicacion
{
    /**
     * Reabastece una ubicación (habitación, espacio o ubicación física) desde una bodega física.
     *
     * @param  array<int, array{producto_variante_id: int, cantidad: float}>  $items
     */
    public function execute(
        string $tipoDestino,
        int $destinoId,
        array $items,
        int $bodegaOrigenId,
        ?int $creadoPorId = null,
        ?string $notas = null,
    ): void {
        if (! in_array($tipoDestino, ['habitacion', 'espacio', 'ubicacion'], true)) {
            throw new \InvalidArgumentException("Tipo de destino inválido: {$tipoDestino}");
        }

        $stockableType = match ($tipoDestino) {
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
        };

        $ubicacionDestinoId = null;
        if ($stockableType === Habitacion::class) {
            $habitacion = Habitacion::find($destinoId);
            $ubicacionDestinoId = $habitacion?->ubicacion_id;
        } elseif ($stockableType === Espacio::class) {
            $espacio = Espacio::find($destinoId);
            $ubicacionDestinoId = $espacio?->ubicacion_id;
        } elseif ($stockableType === Ubicacion::class) {
            $ubicacionDestinoId = $destinoId;
        }

        $consumirStock = app(ConsumirStock::class);

        DB::transaction(function () use ($stockableType, $destinoId, $items, $bodegaOrigenId, $creadoPorId, $notas, $consumirStock, $ubicacionDestinoId) {
            foreach ($items as $item) {
                $variant = ProductoVariante::find($item['producto_variante_id']);
                $productoId = $variant ? $variant->producto_id : 0;

                $detalle = $consumirStock->execute(
                    productoId: $productoId,
                    cantidadRequerida: (float) $item['cantidad'],
                    ubicacionId: $bodegaOrigenId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: (int) $item['producto_variante_id'],
                    creadoPorId: $creadoPorId,
                    notas: $notas,
                    referencia: "Reabastecimiento {$stockableType} #{$destinoId}",
                    ubicacionDestinoId: $ubicacionDestinoId,
                );

                $cantidadConsumida = array_sum(array_column($detalle, 'cantidad'));

                $existing = SharedStock::firstOrNew([
                    'stockable_type' => $stockableType,
                    'stockable_id' => $destinoId,
                    'producto_variante_id' => $item['producto_variante_id'],
                ]);
                $existing->cantidad_actual = ($existing->cantidad_actual ?? 0) + $cantidadConsumida;
                if (! $existing->cantidad_ideal) {
                    $existing->cantidad_ideal = $existing->cantidad_actual;
                }
                $existing->save();
            }
        });
    }
}
