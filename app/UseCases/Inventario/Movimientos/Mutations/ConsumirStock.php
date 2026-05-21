<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Movimientos\Mutations;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class ConsumirStock
{
    /**
     * Consume stock of a product from a specific warehouse using FEFO strategy.
     *
     * @return array<int, array{stock_id: int, lote_id: int|null, cantidad: float}>
     */
    public function execute(
        int $productoId,
        float $cantidadRequerida,
        int $ubicacionId,
        string $tipoMovimiento = 'CONSUMO',
        ?int $productoVarianteId = null,
        ?int $documentoId = null,
        ?string $documentoTipo = null,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null
    ): array {
        if ($cantidadRequerida <= 0) {
            throw new \InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $productoId,
            $cantidadRequerida,
            $ubicacionId,
            $tipoMovimiento,
            $productoVarianteId,
            $documentoId,
            $documentoTipo,
            $creadoPorId,
            $referencia,
            $notas
        ) {
            // 1. Obtener registros de stock en esa bodega
            $stocks = Stock::with(['lote'])
                ->where('producto_id', $productoId)
                ->where('ubicacion_id', $ubicacionId)
                ->where('cantidad', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('lote_id')
                        ->orWhereHas('lote', function ($sub) {
                            $sub->where('estado', EstadoLote::Disponible)
                                ->where(function ($dateQuery) {
                                    $dateQuery->whereNull('fecha_vencimiento')
                                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                                });
                        });
                })
                ->when($productoVarianteId !== null, function ($q) use ($productoVarianteId) {
                    $q->where('producto_variante_id', $productoVarianteId);
                })
                ->get();

            // 2. Ordenar usando FEFO (fecha de vencimiento más próxima primero, nulls al final)
            $ordenados = $stocks->sortBy(function ($stock) {
                return $stock->lote?->fecha_vencimiento?->format('Y-m-d') ?? '9999-12-31';
            })->values();

            $totalDisponible = (float) $ordenados->sum('cantidad');
            if ($totalDisponible < $cantidadRequerida) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente en la bodega. Disponible: %f, Requerido: %f',
                    $totalDisponible,
                    $cantidadRequerida
                ));
            }

            $detalleConsumo = [];
            $restante = $cantidadRequerida;

            foreach ($ordenados as $stock) {
                if ($restante <= 0.0) {
                    break;
                }

                $aConsumir = min((float) $stock->cantidad, $restante);

                // Descontar stock local
                $stock->cantidad -= $aConsumir;
                if ($stock->cantidad <= 0.0) {
                    $stock->delete();
                } else {
                    $stock->save();
                }

                // Descontar del lote global si es un consumo de salida real del sistema
                $isTraslado = in_array($tipoMovimiento, ['TRASLADO', 'MOV_TRANSFERENCIA'], true);
                if ($stock->lote_id !== null && ! $isTraslado) {
                    $lote = $stock->lote;
                    if ($lote) {
                        $lote->cantidad_disponible -= $aConsumir;
                        if ($lote->cantidad_disponible <= 0.0) {
                            $lote->cantidad_disponible = 0.0;
                            $lote->estado = EstadoLote::Agotado;
                        }
                        $lote->save();
                    }
                }

                // Registrar en la bitácora histórica
                MovimientoStock::create([
                    'tipo' => $tipoMovimiento,
                    'lote_id' => $stock->lote_id,
                    'producto_id' => $productoId,
                    'cantidad' => -$aConsumir,
                    'ubicacion_origen_id' => $ubicacionId,
                    'ubicacion_destino_id' => null,
                    'documento_tipo' => $documentoTipo ?: 'consumo',
                    'documento_id' => $documentoId,
                    'referencia' => $referencia ?: "Consumo FEFO bodega {$ubicacionId}",
                    'creado_por_id' => $creadoPorId,
                    'notas' => $notas,
                ]);

                $detalleConsumo[] = [
                    'stock_id' => $stock->id,
                    'lote_id' => $stock->lote_id,
                    'cantidad' => $aConsumir,
                ];

                $restante -= $aConsumir;
            }

            return $detalleConsumo;
        });
    }
}
