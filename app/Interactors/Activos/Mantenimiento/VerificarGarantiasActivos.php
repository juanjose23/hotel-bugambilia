<?php

declare(strict_types=1);

namespace App\Interactors\Activos\Mantenimiento;

use App\Notifications\Activos\NotificadorActivos;
use App\Repository\Queries\Activos\ObtenerActivosConGarantiaPorVencer;

class VerificarGarantiasActivos
{
    public function __construct(
        private readonly ObtenerActivosConGarantiaPorVencer $obtenerActivos,
        private readonly NotificadorActivos $notificador,
    ) {}

    public function ejecutar(): int
    {
        $notificados = 0;

        $this->obtenerActivos->ejecutar(30, function ($activos) use (&$notificados): void {
            foreach ($activos as $activo) {
                $dias = now()->diffInDays($activo->fecha_garantia_fin);

                $this->notificador->garantiaProxima($activo, (int) $dias);
                $notificados++;
            }
        });

        return $notificados;
    }
}
