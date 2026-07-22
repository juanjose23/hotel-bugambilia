<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActivoMantenimiento;

class ActivoMantenimientoRepositorio implements ActivoMantenimientoRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoMantenimiento
    {
        return ActivoMantenimiento::create($datos);
    }

    public function guardar(ActivoMantenimiento $mantenimiento): void
    {
        $mantenimiento->save();
    }

    public function buscarPorId(int $id): ?ActivoMantenimiento
    {
        return ActivoMantenimiento::find($id);
    }

    /** @param array<int, int|string> $estados */
    public function buscarAbiertosPorActivoPlan(int $activoId, int $planId, array $estados): bool
    {
        return ActivoMantenimiento::query()
            ->where('activo_id', $activoId)
            ->where('plan_id', $planId)
            ->whereIn('estado', $estados)
            ->exists();
    }
}
