<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\Repository\Models\Compras\Cotizacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class ElegirCotizacionGanadora
{
    public function __construct(
        private readonly ActualizarEstadosCotizacionesSolicitud $actualizarEstados,
    ) {}

    public function ejecutar(int $cotizacionId): void
    {
        DB::transaction(function () use ($cotizacionId) {
            $cotizacion = Cotizacion::findOrFail($cotizacionId);
            $solicitudId = $cotizacion->solicitud_id;

            Cotizacion::where('solicitud_id', $solicitudId)->update([
                'es_elegida' => false,
                'elegida_por' => null,
                'elegida_en' => null,
            ]);

            DB::table('cotizacion_items')
                ->whereIn('cotizacion_id', function ($query) use ($solicitudId) {
                    $query->select('id')->from('cotizaciones')->where('solicitud_id', $solicitudId);
                })
                ->update(['es_elegido' => false]);

            $cotizacion->update([
                'es_elegida' => true,
                'elegida_por' => Auth::id(),
                'elegida_en' => now(),
            ]);

            $cotizacion->items()->update(['es_elegido' => true]);

            $this->actualizarEstados->ejecutar($solicitudId);
        });
    }
}
