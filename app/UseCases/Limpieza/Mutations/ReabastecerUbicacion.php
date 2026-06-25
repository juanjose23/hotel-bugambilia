<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Models\Catalogos\ProductoVariante;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Shared\Stock as SharedStock;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use Illuminate\Support\Facades\DB;

class ReabastecerUbicacion
{
    /**
     * Reabastece una ubicación (habitación o espacio) desde una bodega física.
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
        if (! in_array($tipoDestino, ['habitacion', 'espacio'], true)) {
            throw new \InvalidArgumentException("Tipo de destino inválido: {$tipoDestino}");
        }

        $consumirStock = app(ConsumirStock::class);

        DB::transaction(function () use ($tipoDestino, $destinoId, $items, $bodegaOrigenId, $creadoPorId, $notas, $consumirStock) {
            foreach ($items as $item) {
                $variante = ProductoVariante::find((int) $item['producto_variante_id']);
                $productoId = $variante->producto_id ?? 0;

                $detalle = $consumirStock->execute(
                    productoId: $productoId,
                    cantidadRequerida: (float) $item['cantidad'],
                    ubicacionId: $bodegaOrigenId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: (int) $item['producto_variante_id'],
                    creadoPorId: $creadoPorId,
                    notas: $notas,
                    referencia: "Reabastecimiento {$tipoDestino} #{$destinoId}",
                );

                $cantidadConsumida = array_sum(array_column($detalle, 'cantidad'));

                $stockableType = $tipoDestino === 'habitacion'
                    ? Habitacion::class
                    : Espacio::class;

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
