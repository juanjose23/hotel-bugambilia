<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\BusinessLogic\Reservas\Data\RegistrarNoShowData;
use App\Events\Reservas\ReservaHabitacionNoShow;
use App\Repository\Models\Reservas\Reserva;

final readonly class RegistrarNoShowHabitacion
{
    public function __construct(
        private CancelarReservaHabitacion $cancelarReservaHabitacion,
    ) {}

    public function ejecutar(RegistrarNoShowData $data): Reserva
    {
        $cancelarData = new CancelarReservaHabitacionData(
            reservaId: $data->reservaId,
            motivo: $data->motivo ?? 'Huésped no se presentó (No-Show)',
            usuarioId: $data->usuarioId,
        );

        $reserva = $this->cancelarReservaHabitacion->ejecutar($cancelarData, esNoShow: true);

        ReservaHabitacionNoShow::dispatch($reserva, $data->motivo);

        return $reserva;
    }
}
