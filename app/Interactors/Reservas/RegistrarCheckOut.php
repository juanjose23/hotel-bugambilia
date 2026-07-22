<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use InvalidArgumentException;

final class RegistrarCheckOut
{
    public function ejecutar(Reserva $reserva): void
    {
        if ($reserva->estado === EstadoReserva::CANCELADA) {
            throw new InvalidArgumentException('No se puede realizar Check-Out en una reserva cancelada.');
        }

        $reserva->estado = EstadoReserva::CHECKED_OUT;
        $reserva->save();
    }
}
