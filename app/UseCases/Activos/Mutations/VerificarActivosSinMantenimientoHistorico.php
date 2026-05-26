<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Models\Activos\Activo;
use App\Services\Activos\NotificadorActivos;

class VerificarActivosSinMantenimientoHistorico
{
    public function __construct(private readonly NotificadorActivos $notificador) {}

    public function execute(): int
    {
        $notificados = 0;

        Activo::query()
            ->with('producto')
            ->whereDoesntHave('mantenimientos')
            ->where('estado', '!=', 3)
            ->chunkById(200, function ($activos) use (&$notificados): void {
                foreach ($activos as $activo) {
                    $this->notificador->sinMantenimientoHistorico($activo);
                    $notificados++;
                }
            });

        return $notificados;
    }
}
