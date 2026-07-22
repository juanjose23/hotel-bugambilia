<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActPlanMantenimiento;

class ActPlanMantenimientoRepositorio implements ActPlanMantenimientoRepositorioInterface
{
    public function guardar(ActPlanMantenimiento $plan): void
    {
        $plan->save();
    }
}
