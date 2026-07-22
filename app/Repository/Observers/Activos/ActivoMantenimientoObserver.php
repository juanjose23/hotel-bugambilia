<?php

declare(strict_types=1);

namespace App\Repository\Observers\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimiento;

class ActivoMantenimientoObserver
{
    public function created(ActivoMantenimiento $mantenimiento): void
    {
        $this->syncActivoEstadoEnProceso($mantenimiento);
    }

    public function updated(ActivoMantenimiento $mantenimiento): void
    {
        $this->syncActivoEstadoEnProceso($mantenimiento);
    }

    private function syncActivoEstadoEnProceso(ActivoMantenimiento $mantenimiento): void
    {
        $rawEstado = $mantenimiento->getRawOriginal('estado');
        if ($rawEstado === null) {
            return;
        }

        $estado = $mantenimiento->estado;

        if ($estado !== EstadoMantenimiento::EnProceso) {
            return;
        }

        $activo = $mantenimiento->activo;
        if (! $activo) {
            return;
        }

        $actual = $activo->estado;

        if ($actual === EstadoActivo::EnMantenimiento) {
            return;
        }

        // Asignar la instancia del enum para respetar el cast del modelo
        $activo->estado = EstadoActivo::EnMantenimiento;
        $activo->save();
    }
}
