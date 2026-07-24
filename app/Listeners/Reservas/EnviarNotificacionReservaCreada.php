<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\ReservaCreada;
use App\Notifications\Reservas\NotificadorReservas;

final class EnviarNotificacionReservaCreada
{
    public function handle(ReservaCreada $event): void
    {
        app(NotificadorReservas::class)->reservaCreada($event->reserva);
    }
}
