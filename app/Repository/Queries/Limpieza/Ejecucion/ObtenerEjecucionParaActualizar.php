<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class ObtenerEjecucionParaActualizar
{
    public function execute(int $ejecucionId): LimpiezaEjecucion
    {
        return LimpiezaEjecucion::query()
            ->lockForUpdate()
            ->findOrFail($ejecucionId);
    }
}
