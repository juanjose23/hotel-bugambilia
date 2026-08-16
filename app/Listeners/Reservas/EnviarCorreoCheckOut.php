<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\CheckOutRegistrado;
use App\Notifications\Reservas\NotificadorHuesped;

final class EnviarCorreoCheckOut
{
    public function handle(CheckOutRegistrado $event): void
    {
        app(NotificadorHuesped::class)->checkOutRegistrado($event->estancia);
    }
}
