<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\ReservaCancelada;
use App\Notifications\Reservas\NotificadorHuesped;

final class EnviarCorreoReservaCancelada
{
    public function handle(ReservaCancelada $event): void
    {
        app(NotificadorHuesped::class)->reservaCancelada($event->reserva);
    }
}
