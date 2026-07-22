<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\Activo;

class ActivoRepositorio implements ActivoRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Activo
    {
        return Activo::create($datos);
    }

    public function buscarPorId(int $id): ?Activo
    {
        return Activo::find($id);
    }

    public function guardar(Activo $activo): void
    {
        $activo->save();
    }
}
