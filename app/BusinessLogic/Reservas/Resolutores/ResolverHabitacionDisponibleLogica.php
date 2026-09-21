<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Resolutores;

use App\Repository\Queries\Reservas\CandidatasHabitacionPorCategoriaQuery;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Selecciona la habitación disponible de la misma categoría que la solicitada.
 * Itera las candidatas priorizando la habitación original y verificando conflictos reales.
 */
final readonly class ResolverHabitacionDisponibleLogica
{
    public function __construct(
        private CandidatasHabitacionPorCategoriaQuery $candidatasQuery,
        private DisponibilidadRecursoQuery $disponibilidad,
    ) {}

    public function resolver(
        int $habitacionSolicitadaId,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        int $adultos,
        int $ninos,
    ): int {
        $salida = $checkOut ?? $checkIn->modify('+1 day');
        $totalPersonas = $adultos + $ninos;

        $resultado = $this->candidatasQuery->ejecutar(
            habitacionSolicitadaId: $habitacionSolicitadaId,
            checkIn: $checkIn,
            salida: $salida,
            totalPersonas: $totalPersonas,
        );

        $candidatas = $resultado['candidatas'];
        $habitacionDisponibleId = null;

        foreach ($candidatas as $candidata) {
            $reservableId = $candidata->reservable_id;
            $conflicto = $reservableId !== null
                && $this->disponibilidad->existeConflicto((int) $reservableId, $checkIn, $salida);

            if (! $conflicto) {
                if ($reservableId !== null) {
                    $this->disponibilidad->bloquear((int) $reservableId);
                }

                $habitacionDisponibleId = (int) $candidata->id;
                break;
            }
        }

        if ($habitacionDisponibleId === null) {
            throw new InvalidArgumentException(
                'No hay habitaciones disponibles de esta categoría para las fechas seleccionadas.'
            );
        }

        return $habitacionDisponibleId;
    }
}
