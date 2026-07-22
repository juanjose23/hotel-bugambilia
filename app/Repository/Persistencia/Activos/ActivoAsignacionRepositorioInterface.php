<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActivoAsignacion;

interface ActivoAsignacionRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoAsignacion;

    public function cerrarAsignacionesVigentes(int $activoId, string $fechaFin, int $estado): void;

    public function buscarVigentePorActivo(int $activoId): ?ActivoAsignacion;

    public function guardar(ActivoAsignacion $asignacion): void;
}
