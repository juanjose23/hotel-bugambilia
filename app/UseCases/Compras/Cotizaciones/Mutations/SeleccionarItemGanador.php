<?php

namespace App\UseCases\Compras\Cotizaciones\Mutations;

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

            CotizacionItem::whereIn('cotizacion_id', function ($query) use ($solicitudId) {
                $query->select('id')->from('cotizaciones')->where('solicitud_id', $solicitudId);
            })
                ->where('producto_id', $productoId)
                ->update(['es_elegido' => false]);

            CotizacionItem::where('cotizacion_id', $cotizacionId)
                ->where('producto_id', $productoId)
                ->update(['es_elegido' => true]);

            if (! $cotizacion->elegida_por) {
                $cotizacion->update([
                    'elegida_por' => Auth::id(),
                    'elegida_en' => now(),
                ]);
            }
            app(ActualizarEstadosCotizacionesSolicitud::class)->execute($solicitudId);
        });
    }
}
