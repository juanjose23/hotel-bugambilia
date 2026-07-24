<?php

declare(strict_types=1);

namespace App\BusinessLogic\CheckIn;

use App\BusinessLogic\Huespedes\ValidarCapacidadEstancia;
use App\BusinessLogic\Huespedes\ValidarDocumentacionHuesped;
use App\BusinessLogic\Huespedes\ValidarTitularUnico;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use DomainException;

final class ValidarRequisitosCheckIn
{
    public function __construct(
        private readonly ValidarCapacidadEstancia $validarCapacidad = new ValidarCapacidadEstancia,
        private readonly ValidarTitularUnico $validarTitular = new ValidarTitularUnico,
        private readonly ValidarDocumentacionHuesped $validarDocumentacion = new ValidarDocumentacionHuesped,
    ) {}

    /**
     * Valida de forma estricta todos los requisitos de negocio previos al Check-in.
     */
    public function validar(Reserva $reserva): void
    {
        $this->validarEstadoReserva($reserva);
        $this->validarHabitacionAsignada($reserva);
        $this->validarCapacidad->validar($reserva);
        $this->validarTitular->validarEstructuraCompleta($reserva);

        if (! $this->validarDocumentacion->estaCompletaParaCheckIn($reserva->huespedes)) {
            throw new DomainException('No se puede completar el Check-in: Existen huéspedes adultos sin documento de identificación verificado.');
        }
    }

    private function validarEstadoReserva(Reserva $reserva): void
    {
        if ($reserva->estado !== EstadoReserva::CONFIRMADA) {
            throw new DomainException("Solo se puede realizar Check-in en reservaciones confirmadas. Estado actual: {$reserva->estado->getLabel()}.");
        }
    }

    private function validarHabitacionAsignada(Reserva $reserva): void
    {
        if ($reserva->habitacion_id === null && $reserva->espacio_id === null) {
            throw new DomainException('No se puede realizar Check-in sin asignar primero una habitación o espacio físico a la reserva.');
        }
    }
}
