<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimiento;

class ObtenerFichasReportesUseCase
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
