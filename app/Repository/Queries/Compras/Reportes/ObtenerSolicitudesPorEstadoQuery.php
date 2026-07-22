<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\SolicitudesEstadoReporteData;
use App\Repository\Models\Compras\Solicitud;

final class ObtenerSolicitudesPorEstadoQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr, ?string $estado = null): SolicitudesEstadoReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $query = Solicitud::with([
            'departamentoSolicitante',
            'colaborador.persona.personaNatural',
            'colaborador.persona.personaJuridica',
        ]);

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        $solicitudes = $query->get();

        $totalSolicitudes = $solicitudes->count();
        $totalAprobadas = $solicitudes->where('estado', 'aprobada')->count();
        $totalRechazadas = $solicitudes->where('estado', 'rechazada')->count();
        $totalPendientes = $solicitudes->where('estado', 'pendiente')->count();

        $data = [
            'total_solicitudes' => $totalSolicitudes,
            'aprobadas' => $totalAprobadas,
            'rechazadas' => $totalRechazadas,
            'pendientes' => $totalPendientes,
        ];

        return new SolicitudesEstadoReporteData(
            data: $data,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
        );
    }
}
