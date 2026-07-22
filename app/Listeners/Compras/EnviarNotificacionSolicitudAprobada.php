<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\SolicitudAprobada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionSolicitudAprobada
{
    public function handle(SolicitudAprobada $event): void
    {
        app(NotificadorCompras::class)->solicitudAprobada($event->solicitud);
    }
}
