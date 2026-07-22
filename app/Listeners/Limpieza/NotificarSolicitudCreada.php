<?php

declare(strict_types=1);

namespace App\Listeners\Limpieza;

use App\Events\Limpieza\SolicitudLimpiezaCreada;
use App\Notifications\Limpieza\NotificadorLimpieza;

final class NotificarSolicitudCreada
{
    public function __construct(
        private readonly NotificadorLimpieza $notificador,
    ) {}

    public function handle(SolicitudLimpiezaCreada $event): void
    {
        $this->notificador->nuevaSolicitudLimpieza($event->solicitud);
    }
}
