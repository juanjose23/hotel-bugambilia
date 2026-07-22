<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\Activo;

class ObtenerActivosConGarantiaPorVencer
{
    public function ejecutar(int $diasLimite, callable $callback): void
    {
        Activo::query()
            ->with('producto')
            ->whereNotNull('fecha_garantia_fin')
            ->whereDate('fecha_garantia_fin', '>=', now()->toDateString())
            ->whereDate('fecha_garantia_fin', '<=', now()->addDays($diasLimite)->toDateString())
            ->chunkById(200, $callback);
    }
}
