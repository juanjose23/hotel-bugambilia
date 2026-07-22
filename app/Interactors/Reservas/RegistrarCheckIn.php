<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use InvalidArgumentException;

final class RegistrarCheckIn
{
    public function ejecutar(Reserva $reserva, ?int $habitacionId = null): void
    {
        if (in_array($reserva->estado, [EstadoReserva::CHECKED_OUT, EstadoReserva::CANCELADA])) {
            throw new InvalidArgumentException("No se puede realizar Check-In en una reserva en estado {$reserva->estado->getLabel()}.");
        }

        if ($habitacionId !== null) {
            $reserva->habitacion_id = max(0, $habitacionId);
        }

        $reserva->estado = EstadoReserva::CHECKED_IN;
        $reserva->save();
    }
}
