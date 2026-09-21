<?php

declare(strict_types=1);

namespace App\Listeners\Shared;

use App\Events\Shared\ReporteIniciado;
use App\Notifications\Reportes\Shared\NotificadorReportes;
use App\Repository\Queries\Usuarios\BuscarUsuarioCuentaQuery;

final readonly class EnviarNotificacionReporteIniciado
{
    public function __construct(
        private NotificadorReportes $notificador,
        private BuscarUsuarioCuentaQuery $buscarUsuario,
    ) {}

    public function handle(ReporteIniciado $event): void
    {
        $usuario = $this->buscarUsuario->porId($event->usuarioId);

        if (! $usuario) {
            return;
        }

        try {
            $this->notificador->reporteEnProceso(
                $usuario,
                $event->codigoReporte,
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
