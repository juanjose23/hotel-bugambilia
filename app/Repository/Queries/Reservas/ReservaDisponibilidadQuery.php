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

    public function existeConflictoEspacio(int $espacioId, DateTimeInterface $fecha, ?string $horaReserva = null, ?int $reservaIgnoradaId = null): bool
    {
        $query = Reserva::query()
            ->where('espacio_id', $espacioId)
            ->whereNotIn('estado', [EstadoReserva::CANCELADA, EstadoReserva::CHECKED_OUT])
            ->whereDate('fecha_check_in', $fecha->format('Y-m-d'));

        if ($reservaIgnoradaId !== null) {
            $query->where('id', '!=', $reservaIgnoradaId);
        }

        if (is_string($horaReserva) && trim($horaReserva) !== '') {
            $query->where('hora_reserva', trim($horaReserva));
        }

        return $query->exists();
    }
}
