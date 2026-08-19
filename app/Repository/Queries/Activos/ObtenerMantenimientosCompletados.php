<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimiento;

final class ObtenerMantenimientosCompletados
{
    public function ejecutar(callable $callback): void
    {
        ActivoMantenimiento::query()
            ->with('activo')
            ->where('estado', EstadoMantenimiento::Completado->value)
            ->chunkById(200, $callback);
    }
}
