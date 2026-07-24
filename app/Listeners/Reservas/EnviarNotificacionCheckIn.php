<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\CheckInRegistrado;
use App\Notifications\Reservas\NotificadorReservas;

final class EnviarNotificacionCheckIn
{
    public function handle(CheckInRegistrado $event): void
    {
        app(NotificadorReservas::class)->checkInRegistrado($event->estancia);
    }
}
