<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Movimientos\Mutations;

use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class TrasladarEntreBodegas
{
    /**
     * Traslada una cantidad de producto en un lote específico de una bodega origen a una bodega destino.
     */
    public function execute(
        int $productoId,
        int $loteId,
        float $cantidad,
        int $origenId,
        int $destinoId,
        ?int $productoVarianteId = null,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a trasladar debe ser mayor a cero.');
        }

        if ($origenId === $destinoId) {
            throw new \InvalidArgumentException('La bodega origen y destino no pueden ser la misma.');
        }

        DB::transaction(function () use (
            $productoId,
            $loteId,
            $cantidad,
            $origenId,
            $destinoId,
            $productoVarianteId,
            $creadoPorId,
            $referencia,
            $notas
        ) {
            // 1. Validar disponibilidad de stock en el origen
            $stockOrigen = Stock::where([
                'producto_id' => $productoId,
                'lote_id' => $loteId,
                'ubicacion_id' => $origenId,
            ])->first();

            if (! $stockOrigen || $stockOrigen->cantidad < $cantidad) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente en la bodega origen. Disponible: %f, Requerido: %f',
                    $stockOrigen ? $stockOrigen->cantidad : 0.0,
                    $cantidad
                ));
            }

            // 2. Descontar del origen
            $stockOrigen->cantidad -= $cantidad;
            if ($stockOrigen->cantidad <= 0.0) {
                $stockOrigen->delete();
            } else {
                $stockOrigen->save();
            }

            // 3. Aumentar en el destino
            $stockDestino = Stock::where([
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => $loteId,
                'ubicacion_id' => $destinoId,
            ])->first();

            if ($stockDestino) {
                $stockDestino->cantidad += $cantidad;
                $stockDestino->save();
            } else {
                Stock::create([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $productoVarianteId,
                    'lote_id' => $loteId,
                    'ubicacion_id' => $destinoId,
                    'cantidad' => $cantidad,
                ]);
            }

            // 4. Registrar movimiento de inventario (TRASLADO)
            MovimientoStock::create([
                'tipo' => 'TRASLADO',
                'lote_id' => $loteId,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'ubicacion_origen_id' => $origenId,
                'ubicacion_destino_id' => $destinoId,
                'documento_tipo' => 'traslado',
                'referencia' => $referencia ?: sprintf('Traslado de bodega %d a bodega %d', $origenId, $destinoId),
                'creado_por_id' => $creadoPorId,
                'notas' => $notas,
            ]);
        });
    }
}
