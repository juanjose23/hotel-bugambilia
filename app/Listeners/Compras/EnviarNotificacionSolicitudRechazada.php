<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\SolicitudRechazada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionSolicitudRechazada
{
    public function handle(SolicitudRechazada $event): void
    {
        app(NotificadorCompras::class)->solicitudRechazada($event->solicitud);
    }
}
