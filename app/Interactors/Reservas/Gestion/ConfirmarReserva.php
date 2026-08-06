<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Enums\Reservas\EstadoReserva;
use App\Events\Reservas\ReservaConfirmada;
use App\Repository\Models\Reservas\Reserva;

final class ConfirmarReserva
{
    public function __construct(
        private readonly CambiarEstadoReserva $cambiarEstado,
    ) {}

    public function ejecutar(Reserva $reserva, ?int $usuarioId = null): void
    {
        $this->cambiarEstado->ejecutar($reserva, EstadoReserva::CONFIRMADA, $usuarioId, 'Reserva confirmada');

        ReservaConfirmada::dispatch($reserva);
    }
}
