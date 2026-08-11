<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\EstadoReserva;
use DomainException;

final class ValidarTransicionEstadoReserva
{
    public function validar(?EstadoReserva $actual, EstadoReserva $nuevo): void
    {
        if ($actual === null) {
            throw new DomainException('La reserva no tiene un estado válido.');
        }

        if (! $this->esPermitida($actual, $nuevo)) {
            throw new DomainException("No se permite cambiar una reserva de {$actual->getLabel()} a {$nuevo->getLabel()}.");
        }
    }

    public function esPermitida(EstadoReserva $actual, EstadoReserva $nuevo): bool
    {
        return match ($actual) {
            EstadoReserva::PENDIENTE => in_array($nuevo, [EstadoReserva::CONFIRMADA, EstadoReserva::CANCELADA, EstadoReserva::NO_SHOW], true),
            EstadoReserva::CONFIRMADA => in_array($nuevo, [EstadoReserva::PARCIALMENTE_CHECKED_IN, EstadoReserva::CHECKED_IN, EstadoReserva::CANCELADA, EstadoReserva::NO_SHOW], true),
            EstadoReserva::PARCIALMENTE_CHECKED_IN => in_array($nuevo, [EstadoReserva::CHECKED_IN, EstadoReserva::PARCIALMENTE_CHECKED_OUT, EstadoReserva::CANCELADA], true),
            EstadoReserva::CHECKED_IN => in_array($nuevo, [EstadoReserva::PARCIALMENTE_CHECKED_OUT, EstadoReserva::CHECKED_OUT], true),
            EstadoReserva::PARCIALMENTE_CHECKED_OUT => $nuevo === EstadoReserva::CHECKED_OUT,
            EstadoReserva::CHECKED_OUT, EstadoReserva::CANCELADA, EstadoReserva::NO_SHOW => false,
        };
    }
}
