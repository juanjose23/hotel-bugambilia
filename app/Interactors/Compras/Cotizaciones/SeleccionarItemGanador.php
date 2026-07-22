<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class SeleccionarItemGanador
{
    public function __construct(
        private readonly ActualizarEstadosCotizacionesSolicitud $actualizarEstados,
    ) {}

    public function ejecutar(int $cotizacionId, int $productoId): void
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
            $this->actualizarEstados->ejecutar($solicitudId);
        });
    }
}
