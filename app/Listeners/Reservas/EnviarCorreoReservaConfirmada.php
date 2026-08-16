<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\ReservaConfirmada;
use App\Notifications\Reservas\NotificadorHuesped;

final class EnviarCorreoReservaConfirmada
{
    public function handle(ReservaConfirmada $event): void
    {
        app(NotificadorHuesped::class)->reservaConfirmada($event->reserva);
    }
}
