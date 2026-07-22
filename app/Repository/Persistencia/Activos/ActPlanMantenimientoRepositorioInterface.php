<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActPlanMantenimiento;

interface ActPlanMantenimientoRepositorioInterface
{
    public function guardar(ActPlanMantenimiento $plan): void;
}
