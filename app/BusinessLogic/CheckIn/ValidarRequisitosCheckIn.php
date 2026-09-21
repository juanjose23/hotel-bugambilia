<?php

declare(strict_types=1);

namespace App\BusinessLogic\CheckIn;

use App\BusinessLogic\Huespedes\ValidarCapacidadEstancia;
use App\BusinessLogic\Huespedes\ValidarDocumentacionHuesped;
use App\BusinessLogic\Huespedes\ValidarTitularUnico;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use DomainException;

final readonly class ValidarRequisitosCheckIn
{
    public function __construct(
        private ValidarCapacidadEstancia $validarCapacidad,
        private ValidarTitularUnico $validarTitular,
        private ValidarDocumentacionHuesped $validarDocumentacion,
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

    public function validarEstadoDetalle(ReservaDetalle $detalle): void
    {
        if (! in_array($detalle->estado, [EstadoReservaDetalle::CONFIRMADO, EstadoReservaDetalle::PENDIENTE], true)) {
            throw new DomainException("El detalle de reserva #{$detalle->id} no está en estado confirmado o pendiente para Check-In.");
        }
    }

    public function validarEstadoHabitacion(Habitacion $habitacion): void
    {
        if (! in_array($habitacion->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
            throw new DomainException("La habitación {$habitacion->nombre} (N° {$habitacion->numero}) no está operacionalmente disponible para Check-In. Estado actual: {$habitacion->estado->getLabel()}.");
        }
    }

    public function validarSinEstanciaActiva(bool $existeEstanciaActiva, int $detalleId): void
    {
        if ($existeEstanciaActiva) {
            throw new DomainException("Ya existe una estancia activa para el detalle de reserva #{$detalleId}.");
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
