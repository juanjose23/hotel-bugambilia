<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use DateTimeInterface;

final class ValidarDisponibilidadHabitacion
{
    public function estaDisponible(int $habitacionId, DateTimeInterface $fechaCheckIn, DateTimeInterface $fechaCheckOut, ?int $ignorarReservaId = null): bool
    {
        $checkInStr = $fechaCheckIn->format('Y-m-d');
        $checkOutStr = $fechaCheckOut->format('Y-m-d');

        $query = Reserva::where('habitacion_id', $habitacionId)
            ->whereNotIn('estado', [EstadoReserva::CANCELADA->value, EstadoReserva::CHECKED_OUT->value])
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                $q->where('fecha_check_in', '<', $checkOutStr)
                    ->where('fecha_check_out', '>', $checkInStr);
            });

        if ($ignorarReservaId !== null) {
            $query->where('id', '!=', $ignorarReservaId);
        }

        return ! $query->exists();
    }
}
