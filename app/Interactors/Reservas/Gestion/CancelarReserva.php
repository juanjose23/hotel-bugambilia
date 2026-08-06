<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\ReservaCancelada;
use App\Repository\Models\Reservas\Reserva;

final class CancelarReserva
{
    public function __construct(
        private readonly CambiarEstadoReserva $cambiarEstado,
    ) {}

    public function ejecutar(Reserva $reserva, ?int $usuarioId = null, string $motivo = 'Reserva cancelada'): void
    {
        $this->cambiarEstado->ejecutar($reserva, EstadoReserva::CANCELADA, $usuarioId, $motivo);

        ReservaCancelada::dispatch($reserva, $motivo);
    }
}
