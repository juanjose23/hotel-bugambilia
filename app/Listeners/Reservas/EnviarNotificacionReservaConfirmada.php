<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\ReservaConfirmada;
use App\Notifications\Reservas\NotificadorReservas;

final class EnviarNotificacionReservaConfirmada
{
    public function handle(ReservaConfirmada $event): void
    {
        app(NotificadorReservas::class)->reservaConfirmada($event->reserva);
    }
}
