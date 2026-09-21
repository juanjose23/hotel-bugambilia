<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\Enums\Compras\EstadoCotizacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Persistencia\Compras\CotizacionRepositorioInterface;

final readonly class ActualizarEstadosCotizacionesSolicitud
{
    public function __construct(
        private CotizacionRepositorioInterface $cotizacionRepositorio,
    ) {}

    public function ejecutar(int $solicitudId): void
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
                $this->cotizacionRepositorio->actualizarEstado(
                    $cot,
                    $nuevoEstado,
                    $nuevoEstado === EstadoCotizacion::Aceptada
                );
            }
        }
    }
}
