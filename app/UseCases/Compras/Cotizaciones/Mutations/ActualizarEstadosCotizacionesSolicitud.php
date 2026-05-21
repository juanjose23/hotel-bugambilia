<?php

namespace App\UseCases\Compras\Cotizaciones\Mutations;

use App\Enums\Compras\EstadoCotizacion;
use App\Models\Compras\Solicitud;

class ActualizarEstadosCotizacionesSolicitud
{
    public function execute(int $solicitudId): void
    {
        $solicitud = Solicitud::with(['items', 'cotizaciones.items'])->findOrFail($solicitudId);
        $totalItemsSolicitud = $solicitud->items->count();

        $cotizaciones = $solicitud->cotizaciones;

        $hayGanadoresEnSolicitud = $cotizaciones->some(fn ($cot) => $cot->items->where('es_elegido', true)->isNotEmpty());

        foreach ($cotizaciones as $cot) {
            $itemsGanadores = $cot->items->where('es_elegido', true)->count();

            $nuevoEstado = EstadoCotizacion::Activa;

            if ($itemsGanadores === $totalItemsSolicitud && $totalItemsSolicitud > 0) {
                $nuevoEstado = EstadoCotizacion::Aceptada;
            } elseif ($itemsGanadores > 0) {
                $nuevoEstado = EstadoCotizacion::AceptadaParcial;
            } elseif ($hayGanadoresEnSolicitud) {
                $nuevoEstado = EstadoCotizacion::Rechazada;
            }

            if ($cot->estado !== $nuevoEstado) {
                $cot->update([
                    'estado' => $nuevoEstado,
                    'es_elegida' => ($nuevoEstado === EstadoCotizacion::Aceptada),
                ]);
            }
        }
    }
}
