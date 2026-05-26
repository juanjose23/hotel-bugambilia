<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Models\Activos\Activo;
use App\Services\Activos\NotificadorActivos;

class VerificarGarantiasActivos
{
    public function __construct(private readonly NotificadorActivos $notificador) {}

    public function execute(): int
    {
        $notificados = 0;

        Activo::query()
            ->with('producto')
            ->whereNotNull('fecha_garantia_fin')
            ->whereDate('fecha_garantia_fin', '>=', now()->toDateString())
            ->whereDate('fecha_garantia_fin', '<=', now()->addDays(30)->toDateString())
            ->chunkById(200, function ($activos) use (&$notificados): void {
                foreach ($activos as $activo) {
                    $dias = now()->diffInDays($activo->fecha_garantia_fin);

                    $this->notificador->garantiaProxima($activo, (int) $dias);
                    $notificados++;
                }
            });

        return $notificados;
    }
}
