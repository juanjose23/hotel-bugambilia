<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Lotes\Mutations;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Services\Inventario\NotificadorInventario;

class RechazarLotesCuarentena
{
    /**
     * @param  int[]  $loteIds
     * @return array<int, array{lote_id: int, codigo_lote: string}>
     */
    public function execute(array $loteIds, string $motivo, ?int $usuarioId = null): array
    {
        $resultado = [];

        $lotes = Lote::whereIn('id', $loteIds)->get();

        $ubicacionMerma = Ubicacion::where('tipo', 'zona')
            ->where(function ($q) {
                $q->where('nombre', 'like', '%merma%')
                    ->orWhere('descripcion', 'like', '%merma%');
            })
            ->first();

        if (! $ubicacionMerma) {
            throw new \RuntimeException(
                'No se ha configurado una ubicación de "Zona de Merma" activa en el sistema.'
            );
        }

        foreach ($lotes as $lote) {

            if ($lote->estado !== EstadoLote::Cuarentena) {
                throw new \InvalidArgumentException(
                    "El lote {$lote->codigo_lote} no está en cuarentena (estado: {$lote->estado->label()})"
                );
            }

            $ubicacionOrigenId = $lote->ubicacion_id;
            $cantidadRechazada = $lote->cantidad_disponible;

            $lote->update([
                'estado' => EstadoLote::Rechazado,
                'cantidad_disponible' => 0.0,
                'ubicacion_id' => $ubicacionMerma->id,
            ]);

            // Eliminar stock de la bodega origen
            Stock::where([
                'lote_id' => $lote->id,
                'ubicacion_id' => $ubicacionOrigenId,
            ])->delete();

            MovimientoStock::create([
                'tipo' => 'MOV_AJUSTE',
                'lote_id' => $lote->id,
                'producto_id' => $lote->producto_id,
                'cantidad' => $cantidadRechazada,
                'ubicacion_origen_id' => $ubicacionOrigenId,
                'ubicacion_destino_id' => $ubicacionMerma->id,
                'documento_tipo' => 'recepcion_item',
                'documento_id' => $lote->recepcion_item_id,
                'referencia' => "Rechazo Lote {$lote->codigo_lote}",
                'creado_por_id' => $usuarioId,
                'notas' => $motivo,
            ]);

            app(NotificadorInventario::class)->loteRechazado($lote, $motivo);

            $resultado[] = [
                'lote_id' => $lote->id,
                'codigo_lote' => $lote->codigo_lote,
            ];
        }

        return $resultado;
    }
}
