<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Repository\Queries\Reservas\CandidatasHabitacionPorCategoriaQuery;
use App\Repository\Queries\Reservas\ReservaDisponibilidadQuery;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Selecciona la habitación disponible de la misma categoría que la solicitada.
 * Itera las candidatas priorizando la habitación original y verificando conflictos reales.
 */
final readonly class ResolverHabitacionDisponibleLogica
{
    public function __construct(
        private CandidatasHabitacionPorCategoriaQuery $candidatasQuery,
        private ReservaDisponibilidadQuery $disponibilidad,
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

        $categoriaId = $resultado['categoriaId'];
        $ubicacionId = $resultado['ubicacionId'];
        $candidatas = $resultado['candidatas'];

        Log::info('[ResolverHabitacionDisponibleLogica] Evaluando disponibilidad para reserva', [
            'categoria_id' => $categoriaId,
            'ubicacion_id' => $ubicacionId,
            'check_in' => $checkIn->format('Y-m-d H:i:s'),
            'check_out' => $salida->format('Y-m-d H:i:s'),
            'habitacion_solicitada_id' => $habitacionSolicitadaId,
            'candidatas_encontradas' => $candidatas->count(),
            'candidatas_ids' => $candidatas->pluck('id')->all(),
        ]);

        $habitacionDisponibleId = null;

        foreach ($candidatas as $candidata) {
            $this->disponibilidad->bloquearHabitacion((int) $candidata->id);
            $conflicto = $this->disponibilidad->existeConflicto((int) $candidata->id, $checkIn, $salida);

            Log::info("[ResolverHabitacionDisponibleLogica] Evaluación habitación #{$candidata->id} ({$candidata->nombre})", [
                'habitacion_id' => $candidata->id,
                'nombre' => $candidata->nombre,
                'existe_conflicto' => $conflicto,
            ]);

            if ($conflicto) {
                continue;
            }

            $habitacionDisponibleId = (int) $candidata->id;
            break;
        }

        if ($habitacionDisponibleId === null) {
            Log::warning('[ResolverHabitacionDisponibleLogica] NINGUNA habitación disponible en la categoría', [
                'categoria_id' => $categoriaId,
                'ubicacion_id' => $ubicacionId,
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $salida->format('Y-m-d'),
            ]);

            throw new InvalidArgumentException('No hay habitaciones disponibles en esta categoría para las fechas seleccionadas.');
        }

        return $habitacionDisponibleId;
    }
}
