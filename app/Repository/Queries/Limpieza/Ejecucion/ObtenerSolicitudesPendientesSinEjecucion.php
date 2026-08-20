<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerSolicitudesPendientesSinEjecucion
{
    /**
     * @return Collection<int, SolicitudLimpieza>
     */
    public function execute(): Collection
    {
        return SolicitudLimpieza::query()
            ->with(['limpiable', 'personal.persona.colaborador', 'creador', 'ejecuciones'])
            ->where('estado', EstadoLimpieza::Pendiente)
            ->whereDoesntHave('ejecuciones')
            ->orderByRaw("CASE WHEN prioridad = 'alta' THEN 0 WHEN prioridad = 'normal' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
