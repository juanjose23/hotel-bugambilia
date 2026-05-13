<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Cotizacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ElegirCotizacionGanadora
{
    public function execute(int $cotizacionId): void
    {
        DB::transaction(function () use ($cotizacionId) {
            $cotizacion = Cotizacion::findOrFail($cotizacionId);
            $solicitud = $cotizacion->solicitud;

            // Desmarcar todas las cotizaciones de esta solicitud
            $solicitud->cotizaciones()->update([
                'es_elegida' => false,
                'elegida_por' => null,
                'elegida_en' => null,
            ]);

            // Desmarcar ítems individuales
            DB::table('cotizacion_items')
                ->whereIn('cotizacion_id', $solicitud->cotizaciones()->pluck('id'))
                ->update(['es_elegido' => false]);

            // Marcar la seleccionada con auditoría completa
            $cotizacion->update([
                'es_elegida' => true,
                'elegida_por' => Auth::id(),
                'elegida_en' => now(),
            ]);

            // Marcar todos sus ítems como elegidos
            $cotizacion->items()->update(['es_elegido' => true]);
        });
    }
}
