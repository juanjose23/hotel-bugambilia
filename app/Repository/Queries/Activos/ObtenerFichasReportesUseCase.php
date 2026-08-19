<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;

final class ObtenerFichasReportesUseCase
{
    public function fichaActivo(Activo $activo): Activo
    {
        return $activo->load([
            'producto',
            'variante',
            'asignacionActiva.asignable',
            'asignaciones.asignadoPor',
            'asignaciones.asignable',
            'mantenimientos.plan.moneda',
            'mantenimientos.plan.proveedor.persona',
            'proveedor.persona',
            'moneda',
        ]);
    }

    public function fichaMantenimiento(ActivoMantenimiento $mantenimiento): ActivoMantenimiento
    {
        return $mantenimiento->load([
            'activo.producto',
            'plan.moneda',
            'plan.proveedor.persona',
            'realizadoPor',
        ]);
    }
}
