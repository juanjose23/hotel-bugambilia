<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Reservas\ValidarTransicionEstadoReserva;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\ObtenerReservaBloqueadaQuery;
use Illuminate\Support\Facades\DB;

final class CambiarEstadoReserva
{
    public function __construct(
        private readonly ValidarTransicionEstadoReserva $validarTransicion,
        private readonly ObtenerReservaBloqueadaQuery $obtenerReservaBloqueada,
        private readonly ReservaRepositorioInterface $reservas,
    ) {}

    public function ejecutar(
        Reserva $reserva,
        EstadoReserva $estado,
        ?int $usuarioId,
        string $motivo,
    ): void {
        DB::transaction(function () use ($reserva, $estado, $usuarioId, $motivo): void {
            $bloqueada = $this->obtenerReservaBloqueada->ejecutar($reserva->id);
            $this->validarTransicion->validar($bloqueada->estado, $estado);
            $this->reservas->cambiarEstado($bloqueada, $estado, $usuarioId, $motivo);
            $reserva->setRawAttributes($bloqueada->getAttributes(), true);
        });
    }
}
