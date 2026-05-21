<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Dotacion;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class RegistrarDevolucion
{
    /**
     * Registra la devolución de consumibles a una bodega,
     * incrementando el stock disponible y registrando el movimiento correspondientemente.
     */
    public function execute(
        int $productoId,
        float $cantidad,
        int $ubicacionId,
        ?int $productoVarianteId = null,
        ?int $loteId = null,
        ?int $usuarioId = null,
        ?string $referencia = null,
        ?string $notas = null
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a devolver debe ser mayor a cero.');
        }

        DB::transaction(function () use (
            $productoId,
            $cantidad,
            $ubicacionId,
            $productoVarianteId,
            $loteId,
            $usuarioId,
            $referencia,
            $notas
        ) {
            $deducedLoteId = $loteId;

            // Si no se provee lote, tratar de deducir uno adecuado
            if ($deducedLoteId === null) {
                // Buscar algún registro de stock existente para ese producto/variante en la bodega
                $existente = Stock::where('producto_id', $productoId)
                    ->where('ubicacion_id', $ubicacionId)
                    ->when($productoVarianteId !== null, function ($q) use ($productoVarianteId) {
                        $q->where('producto_variante_id', $productoVarianteId);
                    })
                    ->whereNotNull('lote_id')
                    ->first();

                if ($existente) {
                    $deducedLoteId = $existente->lote_id;
                } else {
                    // Buscar el lote más reciente creado para este producto
                    $loteMasReciente = Lote::where('producto_id', $productoId)
                        ->when($productoVarianteId !== null, function ($q) use ($productoVarianteId) {
                            $q->where('producto_variante_id', $productoVarianteId);
                        })
                        ->latest()
                        ->first();

                    if ($loteMasReciente) {
                        $deducedLoteId = $loteMasReciente->id;
                    }
                }
            }

            // Incrementar lote global si corresponde
            if ($deducedLoteId !== null) {
                $lote = Lote::find($deducedLoteId);
                if ($lote) {
                    $lote->cantidad_disponible += $cantidad;
                    // Si estaba agotado y ahora tiene cantidad, habilitarlo
                    if ($lote->estado === EstadoLote::Agotado && $lote->cantidad_disponible > 0) {
                        $lote->estado = EstadoLote::Disponible;
                    }
                    $lote->save();
                }
            }

            // Registrar/Actualizar Stock en la bodega
            $stock = Stock::where([
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => $deducedLoteId,
                'ubicacion_id' => $ubicacionId,
            ])->first();

            if ($stock) {
                $stock->cantidad += $cantidad;
                $stock->save();
            } else {
                Stock::create([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $productoVarianteId,
                    'lote_id' => $deducedLoteId,
                    'ubicacion_id' => $ubicacionId,
                    'cantidad' => $cantidad,
                ]);
            }

            // Registrar movimiento histórico de stock
            MovimientoStock::create([
                'tipo' => 'DEVOLUCION_BODEGA',
                'lote_id' => $deducedLoteId,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'ubicacion_origen_id' => null,
                'ubicacion_destino_id' => $ubicacionId,
                'documento_tipo' => 'devolucion',
                'referencia' => $referencia ?: "Devolución de consumibles a bodega {$ubicacionId}",
                'creado_por_id' => $usuarioId,
                'notas' => $notas,
            ]);
        });
    }
}
