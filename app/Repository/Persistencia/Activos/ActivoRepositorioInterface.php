<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\Activo;

interface ActivoRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Activo;

    public function buscarPorId(int $id): ?Activo;

    public function guardar(Activo $activo): void;
}
