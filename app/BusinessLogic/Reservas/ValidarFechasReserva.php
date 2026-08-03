<?php

namespace App\BusinessLogic\Reservas;

use DateMalformedStringException;
use DateTimeImmutable;
use DomainException;

final class ValidarFechasReserva
{
    /**
     * @throws DateMalformedStringException
     */
    public function validar(DateTimeImmutable $checkIn, ?string $horaReservaStr): void
    {
        $hoyInicio = new DateTimeImmutable(now()->startOfDay()->toDateTimeString());
        if ($checkIn < $hoyInicio) {
            throw new DomainException('No es posible realizar una reservación para fechas pasadas.');
        }

        if ($checkIn->format('Y-m-d') === now()->format('Y-m-d') && $horaReservaStr !== null && $horaReservaStr !== '') {
            if ($horaReservaStr < now()->format('H:i')) {
                throw new DomainException('No es posible realizar una reservación para una hora que ya ha transcurrido hoy.');
            }
        }
    }
}
