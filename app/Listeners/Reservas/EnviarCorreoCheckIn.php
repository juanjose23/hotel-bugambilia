<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\CheckInRegistrado;
use App\Notifications\Reservas\NotificadorHuesped;

final class EnviarCorreoCheckIn
{
    public function handle(CheckInRegistrado $event): void
    {
        app(NotificadorHuesped::class)->checkInRegistrado($event->estancia);
    }
}
