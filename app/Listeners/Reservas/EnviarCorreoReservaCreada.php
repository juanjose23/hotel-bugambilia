<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\ReservaCreada;
use App\Notifications\Reservas\NotificadorHuesped;

final class EnviarCorreoReservaCreada
{
    public function handle(ReservaCreada $event): void
    {
        app(NotificadorHuesped::class)->reservaCreada($event->reserva);
    }
}
