<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Repository\Models\Activos\ActPlanMantenimiento;
use Illuminate\Support\Collection;

class ObtenerPlanesMantenimientoPreventivosActivos
{
    /** @return Collection<int, ActPlanMantenimiento> */
    public function ejecutar(): Collection
    {
        return ActPlanMantenimiento::query()
            ->where('tipo', 'preventivo')
            ->where('estado', EstadoPlanMantenimiento::Activo)
            ->whereNotNull('frecuencia_dias')
            ->has('activos')
            ->get();
    }
}
