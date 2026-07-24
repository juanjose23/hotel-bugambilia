<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\ReservaCancelada;
use App\Notifications\Reservas\NotificadorReservas;

final class EnviarNotificacionReservaCancelada
{
    public function handle(ReservaCancelada $event): void
    {
        app(NotificadorReservas::class)->reservaCancelada($event->reserva);
    }
}
