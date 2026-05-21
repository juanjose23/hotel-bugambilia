<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Lotes\Mutations;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Services\Inventario\NotificadorInventario;
use App\UseCases\Inventario\Services\PutawayPolicy;

class LiberarLotesCuarentena
{
    /**
     * @param  int[]  $loteIds
     * @return array<int, array{lote_id: int, codigo_lote: string}>
     */
    public function execute(array $loteIds, ?int $usuarioId = null): array
    {
        $resultado = [];
        $errores = [];

        $lotes = Lote::whereIn('id', $loteIds)->get();

        foreach ($lotes as $lote) {

            if ($lote->estado !== EstadoLote::Cuarentena) {
                $errores[] = [
                    'lote_id' => $lote->id,
                    'error' => "El lote {$lote->codigo_lote} no está en cuarentena (estado: {$lote->estado->label()})",
                ];

                continue;
            }

            $ubicacionOrigenId = $lote->ubicacion_id;
            $nuevaUbicacion = PutawayPolicy::sugerirUbicacion();

            $lote->update([
                'estado' => EstadoLote::Disponible,
                'ubicacion_id' => $nuevaUbicacion->id,
            ]);

            // Sincronizar el stock en inv_stock
            $stock = Stock::where([
                'lote_id' => $lote->id,
                'ubicacion_id' => $ubicacionOrigenId,
            ])->first();

            if ($stock) {
                if ($ubicacionOrigenId !== $nuevaUbicacion->id) {
                    $stockDestino = Stock::where([
                        'producto_id' => $lote->producto_id,
                        'producto_variante_id' => $lote->producto_variante_id,
                        'lote_id' => $lote->id,
                        'ubicacion_id' => $nuevaUbicacion->id,
                    ])->first();

                    if ($stockDestino) {
                        $stockDestino->cantidad += $stock->cantidad;
                        $stockDestino->save();
                        $stock->delete();
                    } else {
                        $stock->update([
                            'ubicacion_id' => $nuevaUbicacion->id,
                        ]);
                    }
                }
            } else {
                Stock::create([
                    'producto_id' => $lote->producto_id,
                    'producto_variante_id' => $lote->producto_variante_id,
                    'lote_id' => $lote->id,
                    'ubicacion_id' => $nuevaUbicacion->id,
                    'cantidad' => $lote->cantidad_disponible,
                ]);
            }

            MovimientoStock::create([
                'tipo' => 'MOV_TRANSFERENCIA',
                'lote_id' => $lote->id,
                'producto_id' => $lote->producto_id,
                'cantidad' => $lote->cantidad_disponible,
                'ubicacion_origen_id' => $ubicacionOrigenId,
                'ubicacion_destino_id' => $nuevaUbicacion->id,
                'documento_tipo' => 'liberacion_cuarentena',
                'documento_id' => $lote->id,
                'referencia' => "Liberación {$lote->codigo_lote}",
                'creado_por_id' => $usuarioId,
                'notas' => 'Liberado de cuarentena a almacenamiento',
            ]);

            app(NotificadorInventario::class)->loteLiberado($lote);

            $resultado[] = [
                'lote_id' => $lote->id,
                'codigo_lote' => $lote->codigo_lote,
            ];
        }

        return $resultado;
    }
}
