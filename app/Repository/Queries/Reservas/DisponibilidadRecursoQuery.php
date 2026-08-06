<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Reservas\EstadoReservaDetalle;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\ReservaDetalle;
use DateTimeInterface;

final class DisponibilidadRecursoQuery
{
    public function bloquear(int $recursoId): RecursoReservable
    {
        return RecursoReservable::query()->lockForUpdate()->findOrFail($recursoId);
    }

    public function existeConflicto(
        int $recursoId,
        DateTimeInterface $inicio,
        DateTimeInterface $fin,
        ?int $reservaExcluidaId = null,
    ): bool {
        return ReservaDetalle::query()
            ->when($reservaExcluidaId !== null, fn ($query) => $query->where('reserva_id', '!=', $reservaExcluidaId))
            ->where('reservable_id', $recursoId)
            ->whereNotIn('estado', [EstadoReservaDetalle::CANCELADO, EstadoReservaDetalle::COMPLETADO])
            ->where('fecha_inicio', '<', $fin)
            ->where(function ($query) use ($inicio): void {
                $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>', $inicio);
            })
            ->exists();
    }
}
