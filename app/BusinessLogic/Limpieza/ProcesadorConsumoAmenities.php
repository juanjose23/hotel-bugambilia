<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza;

use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Shared\Stock as SharedStock;

class ProcesadorConsumoAmenities
{
    /** @param array<int, float|string> $consumosCantidad */
    public function procesar(array $consumosCantidad, int $ejecucionId, ?int $usuarioId): void
    {
        foreach ($consumosCantidad as $stockId => $qty) {
            $qty = (float) $qty;
            if ($qty <= 0) {
                continue;
            }

            $sharedStock = SharedStock::where('id', $stockId)->lockForUpdate()->firstOrFail();
            $sharedStock->cantidad_actual = max(0.0, (float) $sharedStock->cantidad_actual - $qty);
            $sharedStock->save();

            $variante = $sharedStock->variante;
            $productoId = $variante->producto_id ?? 0;

            $costoUnitario = null;
            $costoTotal = null;
            if ($sharedStock->lote_id) {
                $lote = Lote::find($sharedStock->lote_id);
                if ($lote) {
                    $costoUnitario = $lote->costo_unitario;
                    $costoTotal = $costoUnitario !== null ? $costoUnitario * abs($qty) : null;
                }
            }

            MovimientoStock::create([
                'tipo' => 'CONSUMO',
                'lote_id' => $sharedStock->lote_id,
                'producto_id' => $productoId,
                'cantidad' => -$qty,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'ubicacion_origen_id' => null,
                'ubicacion_destino_id' => null,
                'documento_tipo' => 'limp_ejecuciones',
                'documento_id' => $ejecucionId,
                'referencia' => 'Consumo de amenity en habitación',
                'creado_por_id' => $usuarioId,
                'notas' => "Consumo registrado en ejecución #{$ejecucionId}",
            ]);
        }
    }
}
