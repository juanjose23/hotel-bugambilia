<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\SolicitudCreada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionSolicitudCreada
{
    public function handle(SolicitudCreada $event): void
    {
        app(NotificadorCompras::class)->solicitudCreada($event->solicitud);
    }
}
