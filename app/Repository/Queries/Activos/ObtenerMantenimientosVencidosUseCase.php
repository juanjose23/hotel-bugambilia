<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerMantenimientosVencidosUseCase
{
    /**
     * @return Collection<int, ActivoMantenimiento>
     */
    public function obtenerProgramadosVencidos(): Collection
    {
        $ayer = now()->subDay()->toDateString();

        return ActivoMantenimiento::query()
            ->with(['activo', 'plan', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->where('fecha_programada', '<=', $ayer)
            ->get();
    }

    /**
     * @return Collection<int, ActivoMantenimiento>
     */
    public function obtenerEnProcesoSobrepasados(): Collection
    {
        return ActivoMantenimiento::query()
            ->with(['activo', 'plan', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::EnProceso)
            ->where('fecha_programada', '<=', now()->subDays(15)->toDateString())
            ->get();
    }
}
