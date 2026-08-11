<?php

declare(strict_types=1);

namespace App\BusinessLogic\CheckIn;

use App\BusinessLogic\Huespedes\ValidarCapacidadEstancia;
use App\BusinessLogic\Huespedes\ValidarDocumentacionHuesped;
use App\BusinessLogic\Huespedes\ValidarTitularUnico;
use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
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
    public function validar(Reserva $reserva, ?ReservaDetalle $detalle = null): void
    {
        $this->validarEstadoReserva($reserva);
        $this->validarHabitacionAsignada($reserva, $detalle);
        $this->validarCapacidad->validar($reserva, $detalle);
        $this->validarTitular->validarEstructuraCompleta($reserva);

        if (! $this->validarDocumentacion->estaCompletaParaCheckIn($reserva->huespedes)) {
            throw new DomainException('No se puede completar el Check-in: Existen huéspedes adultos sin documento de identificación verificado.');
        }
    }

    private function validarEstadoReserva(Reserva $reserva): void
    {
        if (! in_array($reserva->estado, [EstadoReserva::CONFIRMADA, EstadoReserva::PARCIALMENTE_CHECKED_IN], true)) {
            throw new DomainException("Solo se puede realizar Check-in en reservaciones confirmadas o parcialmente ingresadas. Estado actual: {$reserva->estado->getLabel()}.");
        }
    }

    private function validarHabitacionAsignada(Reserva $reserva, ?ReservaDetalle $detalle = null): void
    {
        if ($detalle !== null) {
            return;
        }

        $tieneDetalleReservable = $reserva->detalles()->whereNotNull('reservable_id')->exists();

        if ($reserva->habitacion_id === null && $reserva->espacio_id === null && ! $tieneDetalleReservable) {
            throw new DomainException('No se puede realizar Check-in sin asignar primero una habitación o espacio físico a la reserva.');
        }
    }
}
