<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Cotizacion;
use App\Models\Compras\CotizacionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeleccionarItemGanador
{
    public function execute(int $cotizacionId, int $productoId): void
    {
        DB::transaction(function () use ($cotizacionId, $productoId) {
            $cotizacion = Cotizacion::findOrFail($cotizacionId);
            $solicitudId = $cotizacion->solicitud_id;

            // Obtener todas las cotizaciones de la misma solicitud
            $cotizacionesIds = Cotizacion::where('solicitud_id', $solicitudId)->pluck('id');

            // Desmarcar este producto de todas las cotizaciones de la solicitud
            CotizacionItem::whereIn('cotizacion_id', $cotizacionesIds)
                ->where('producto_id', $productoId)
                ->update(['es_elegido' => false]);

            // Marcar el producto en la cotización específica
            CotizacionItem::where('cotizacion_id', $cotizacionId)
                ->where('producto_id', $productoId)
                ->update(['es_elegido' => true]);

            // Actualizar auditoría en la cabecera si no estaba marcada
            if (! $cotizacion->elegida_por) {
                $cotizacion->update([
                    'elegida_por' => Auth::id(),
                    'elegida_en' => now(),
                ]);
            }
        });
    }
}
