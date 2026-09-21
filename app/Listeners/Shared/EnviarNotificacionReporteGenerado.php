<?php

declare(strict_types=1);

namespace App\Listeners\Shared;

use App\Events\Shared\ReporteGenerado;
use App\Notifications\Reportes\Shared\NotificadorReportes;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;

final readonly class EnviarNotificacionReporteGenerado
{
    public function __construct(
        private NotificadorReportes $notificador,
        private BuscarUsuarioCuentaQuery $buscarUsuario,
    ) {}

    public function handle(ReporteGenerado $event): void
    {
        $usuario = $this->buscarUsuario->porId($event->usuarioId);

        if (! $usuario) {
            return;
        }

        try {
            $this->notificador->reporteListo(
                $usuario,
                $event->codigoReporte,
                $event->urlDescarga,
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
