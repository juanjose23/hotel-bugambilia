<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Reservas\Reserva;

final class CancelarReserva
{
    public function __construct(
        private readonly CancelarReservaHabitacion $cancelarReservaHabitacion,
    ) {}

    public function ejecutar(Reserva $reserva, ?int $usuarioId = null, string $motivo = 'Reserva cancelada'): void
    {
        $data = new CancelarReservaHabitacionData(
            reservaId: $reserva->id,
            motivo: $motivo,
            usuarioId: $usuarioId,
        );

        $this->cancelarReservaHabitacion->ejecutar($data);
    }
}
