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

    /**
     * Bloquea múltiples recursos reservables para prevenir condiciones de carrera.
     *
     * Ejecuta un SELECT FOR UPDATE en batch para serializar el acceso a los recursos
     * durante operaciones concurrentes de reserva.
     *
     * @param  array<int, int>  $recursoIds  IDs de los recursos a bloquear
     */
    public function bloquearRecursos(array $recursoIds): void
    {
        if ($recursoIds === []) {
            return;
        }

        RecursoReservable::query()
            ->whereIn('id', $recursoIds)
            ->lockForUpdate()
            ->get();
    }

    /**
     * Verifica conflictos de disponibilidad para múltiples recursos en una sola query.
     *
     * Optimiza el rendimiento al evitar N+1 queries cuando se valida disponibilidad
     * de múltiples recursos (ej: habitaciones adicionales en una reserva).
     *
     * Un conflicto existe cuando el recurso tiene un detalle en estado distinto a
     * CANCELADO o COMPLETADO cuyo rango de fechas se superpone con el solicitado.
     *
     * @param  array<int, int>  $recursoIds  IDs de los recursos a verificar
     * @return array<int, int> IDs de recursos con conflicto (sin duplicados)
     */
    public function existeConflictos(
        array $recursoIds,
        DateTimeInterface $inicio,
        DateTimeInterface $fin,
        ?int $reservaExcluidaId = null,
    ): array {
        if ($recursoIds === []) {
            return [];
        }

        /** @var list<int> */
        return ReservaDetalle::query()
            ->when($reservaExcluidaId !== null, fn ($query) => $query->where('reserva_id', '!=', $reservaExcluidaId))
            ->whereIn('reservable_id', $recursoIds)
            ->whereNotIn('estado', [EstadoReservaDetalle::CANCELADO, EstadoReservaDetalle::COMPLETADO])
            ->where('fecha_inicio', '<', $fin)
            ->where(function ($query) use ($inicio): void {
                $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>', $inicio);
            })
            ->pluck('reservable_id')
            ->unique()
            ->values()
            ->all();
    }
}
