<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Reposiciones\Mutations;

use App\Models\Inventario\Reposicion;
use App\Models\Inventario\Stock;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProcesarReposicion
{
    public function __construct(
        private readonly ConsumirStock $consumirStock,
    ) {}

    /**
     * Procesa una orden de reposición, consumiendo stock de la bodega origen
     * y transfiriéndolo al destino configurado en la orden usando FEFO.
     */
    public function execute(int $reposicionId, ?int $procesadoPorId = null): void
    {
        DB::transaction(function () use ($reposicionId, $procesadoPorId) {
            $reposicion = Reposicion::with(['items'])->findOrFail($reposicionId);

            if ($reposicion->estado !== 'pendiente') {
                throw new \RuntimeException('La orden de reposición ya ha sido procesada o cancelada.');
            }

            $origenId = $reposicion->origen_id;
            $destinoId = $reposicion->destino_id;

            foreach ($reposicion->items as $item) {
                $productoId = (int) $item->producto_id;
                $varianteId = $item->producto_variante_id ? (int) $item->producto_variante_id : null;
                $cantidadSolicitada = (float) $item->cantidad_solicitada;

                if ($cantidadSolicitada <= 0) {
                    continue;
                }

                try {
                    // Consumir del origen usando la estrategia FEFO
                    $consumidos = $this->consumirStock->execute(
                        productoId: $productoId,
                        cantidadRequerida: $cantidadSolicitada,
                        ubicacionId: $origenId,
                        tipoMovimiento: 'TRASLADO',
                        productoVarianteId: $varianteId,
                        documentoId: $reposicion->id,
                        documentoTipo: 'reposicion',
                        creadoPorId: $procesadoPorId,
                        referencia: sprintf('Reposición #%s de origen %d a destino %d', $reposicion->codigo, $origenId, $destinoId)
                    );

                    $cantidadSurtida = 0.0;

                    // Asignar en el destino
                    foreach ($consumidos as $con) {
                        $stockDestino = Stock::where([
                            'producto_id' => $productoId,
                            'producto_variante_id' => $varianteId,
                            'lote_id' => $con['lote_id'],
                            'ubicacion_id' => $destinoId,
                        ])->first();

                        if ($stockDestino) {
                            $stockDestino->cantidad += $con['cantidad'];
                            $stockDestino->save();
                        } else {
                            Stock::create([
                                'producto_id' => $productoId,
                                'producto_variante_id' => $varianteId,
                                'lote_id' => $con['lote_id'],
                                'ubicacion_id' => $destinoId,
                                'cantidad' => $con['cantidad'],
                            ]);
                        }

                        $cantidadSurtida += $con['cantidad'];
                    }

                    // Actualizar la cantidad surtida del item
                    $item->update([
                        'cantidad_surtida' => $cantidadSurtida,
                    ]);

                } catch (\RuntimeException $e) {
                    throw new \RuntimeException(sprintf(
                        'No hay suficiente stock del producto ID %d en la bodega origen (%d) para procesar la reposición #%s. Detalle: %s',
                        $productoId,
                        $origenId,
                        $reposicion->codigo,
                        $e->getMessage()
                    ));
                }
            }

            // 4. Actualizar estado de la orden
            $reposicion->update([
                'estado' => 'procesada',
                'procesado_por_id' => $procesadoPorId,
                'fecha_proceso' => Carbon::now(),
            ]);
        });
    }
}
