<?php

declare(strict_types=1);

namespace App\Listeners\Shared;

use App\Events\Shared\ReporteGenerado;
use App\Notifications\Reportes\Shared\NotificadorReportes;
use App\Repository\Models\User;

final class EnviarNotificacionReporteGenerado
{
    public function __construct(
        private readonly NotificadorReportes $notificador,
    ) {}

    public function handle(ReporteGenerado $event): void
    {
        $usuario = User::find($event->usuarioId);

        if (! $usuario) {
            return;
        }

        $this->notificador->reporteListo(
            $usuario,
            $event->codigoReporte,
            $event->urlDescarga,
        );
    }
}
