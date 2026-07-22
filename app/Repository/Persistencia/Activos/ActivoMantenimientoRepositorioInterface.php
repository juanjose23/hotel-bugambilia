<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActivoMantenimiento;

interface ActivoMantenimientoRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoMantenimiento;

    public function guardar(ActivoMantenimiento $mantenimiento): void;

    public function buscarPorId(int $id): ?ActivoMantenimiento;

    /** @param array<int, int|string> $estados */
    public function buscarAbiertosPorActivoPlan(int $activoId, int $planId, array $estados): bool;
}
