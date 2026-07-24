<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use DateTimeInterface;

final class ReservaDisponibilidadQuery
{
    public function bloquearHabitacion(int $habitacionId): void
    {
        Habitacion::query()->whereKey($habitacionId)->lockForUpdate()->firstOrFail();
    }

    public function existeConflicto(int $habitacionId, DateTimeInterface $entrada, DateTimeInterface $salida): bool
    {
        return Reserva::query()
            ->where('habitacion_id', $habitacionId)
            ->whereNotIn('estado', [EstadoReserva::CANCELADA, EstadoReserva::CHECKED_OUT])
            ->where('fecha_check_in', '<', $salida->format('Y-m-d'))
            ->where('fecha_check_out', '>', $entrada->format('Y-m-d'))
            ->exists();
    }
}
