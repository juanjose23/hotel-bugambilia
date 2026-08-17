<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\SolicitudesEstadoReporteData;
use App\Repository\Models\Compras\Solicitud;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ObtenerSolicitudesPorEstadoQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr, ?string $estado = null): SolicitudesEstadoReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $solicitudes = $this->consultarSolicitudes($fechaInicio, $fechaFin, $estado);

        return new SolicitudesEstadoReporteData(
            data: $solicitudes->values()->all(),
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
        );
    }

    /**
     * @return array{CarbonInterface, CarbonInterface}
     */
    private function resolverRangoFechas(?string $fechaInicioStr, ?string $fechaFinStr): array
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        return [$fechaInicio, $fechaFin];
    }

    /**
     * @return EloquentCollection<int, Solicitud>
     */
    private function consultarSolicitudes(CarbonInterface $fechaInicio, CarbonInterface $fechaFin, ?string $estado): EloquentCollection
    {
        $query = Solicitud::with([
            'departamentoSolicitante',
            'colaborador.persona.personaNatural',
            'colaborador.persona.personaJuridica',
        ]);

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        /** @var EloquentCollection<int, Solicitud> $solicitudes */
        $solicitudes = $query->get();

        return $solicitudes;
    }
}
