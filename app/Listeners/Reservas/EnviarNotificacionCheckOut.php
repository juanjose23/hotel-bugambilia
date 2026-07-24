<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\CheckOutRegistrado;
use App\Notifications\Reservas\NotificadorReservas;

final class EnviarNotificacionCheckOut
{
    public function handle(CheckOutRegistrado $event): void
    {
        app(NotificadorReservas::class)->checkOutRegistrado($event->estancia);
    }
}
