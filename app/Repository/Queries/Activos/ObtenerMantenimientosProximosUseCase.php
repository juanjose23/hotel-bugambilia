<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Illuminate\Database\Eloquent\Collection;

class ObtenerMantenimientosProximosUseCase
{
    /**
     * @return Collection<int, ActivoMantenimiento>
     */
    public function execute(int $dias = 7): Collection
    {
        return ActivoMantenimiento::query()
            ->with(['activo', 'plan', 'realizadoPor'])
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereBetween('fecha_programada', [now()->toDateString(), now()->addDays($dias)->toDateString()])
            ->get();
    }
}
