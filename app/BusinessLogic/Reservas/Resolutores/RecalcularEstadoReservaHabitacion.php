<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Resolutores;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;

final readonly class RecalcularEstadoReservaHabitacion
{
    public function __construct(
        private ReservaRepositorioInterface $reservaRepositorio,
    ) {}

    /**
     * Evalúa y devuelve el nuevo estado de la reserva basado únicamente en sus detalles principales de habitación.
     */
    public function calcularNuevoEstado(Reserva $reserva): EstadoReserva
    {
        $detalles = $this->reservaRepositorio->detallesPrincipalesDe($reserva);

        $detallesHabitacion = $detalles->filter(function (ReservaDetalle $d): bool {
            return $d->reservable !== null && $d->reservable->tipo === TipoRecursoReservable::HABITACION;
        });

        if ($detallesHabitacion->isEmpty()) {
            return $reserva->estado;
        }

        $total = $detallesHabitacion->count();
        $pendientes = $detallesHabitacion->where('estado', EstadoReservaDetalle::PENDIENTE)->count();
        $confirmados = $detallesHabitacion->where('estado', EstadoReservaDetalle::CONFIRMADO)->count();
        $enUso = $detallesHabitacion->where('estado', EstadoReservaDetalle::EN_USO)->count();
        $completados = $detallesHabitacion->where('estado', EstadoReservaDetalle::COMPLETADO)->count();
        $cancelados = $detallesHabitacion->where('estado', EstadoReservaDetalle::CANCELADO)->count();

        if ($cancelados === $total) {
            return EstadoReserva::CANCELADA;
        }

        if ($completados === $total) {
            return EstadoReserva::CHECKED_OUT;
        }

        if ($enUso === $total) {
            return EstadoReserva::CHECKED_IN;
        }

        if ($confirmados === $total) {
            return EstadoReserva::CONFIRMADA;
        }

        if ($pendientes === $total) {
            return EstadoReserva::PENDIENTE;
        }

        if ($enUso > 0 && ($confirmados > 0 || $pendientes > 0)) {
            return EstadoReserva::PARCIALMENTE_CHECKED_IN;
        }

        if ($completados > 0 && ($enUso > 0 || $confirmados > 0 || $pendientes > 0)) {
            return EstadoReserva::PARCIALMENTE_CHECKED_OUT;
        }

        return $reserva->estado;
    }

    public function ejecutar(Reserva $reserva): EstadoReserva
    {
        $nuevoEstado = $this->calcularNuevoEstado($reserva);
        if ($reserva->estado !== $nuevoEstado) {
            $this->reservaRepositorio->actualizarDatosGenerales($reserva, ['estado' => $nuevoEstado]);
            $reserva->estado = $nuevoEstado;
        }

        return $nuevoEstado;
    }
}
