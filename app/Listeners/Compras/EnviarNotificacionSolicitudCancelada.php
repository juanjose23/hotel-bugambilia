<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\SolicitudCancelada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionSolicitudCancelada
{
    public function handle(SolicitudCancelada $event): void
    {
        app(NotificadorCompras::class)->solicitudCancelada($event->solicitud);
    }
}
