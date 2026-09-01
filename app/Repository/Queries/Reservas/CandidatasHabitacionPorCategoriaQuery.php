<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Repository\Models\Habitaciones\Habitacion;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Obtiene las habitaciones candidatas disponibles de una categoría para un rango de fechas dado.
 * Prioriza la habitación solicitada originalmente y excluye las que tengan conflictos de reserva activos.
 */
final readonly class CandidatasHabitacionPorCategoriaQuery
{
    /**
     * Retorna metadatos de la habitación solicitada y la lista de candidatas disponibles.
     *
     * @return array{categoriaId: int, ubicacionId: int, candidatas: Collection<int, Habitacion>}
     */
    public function ejecutar(
        int $habitacionSolicitadaId,
        DateTimeImmutable $checkIn,
        DateTimeImmutable $salida,
        int $totalPersonas,
    ): array {
        /** @var Habitacion $habitacionSolicitada */
        $habitacionSolicitada = Habitacion::query()
            ->select(['id', 'categoria_id', 'ubicacion_id'])
            ->findOrFail($habitacionSolicitadaId);

        $categoriaId = (int) $habitacionSolicitada->categoria_id;
        $ubicacionId = (int) $habitacionSolicitada->ubicacion_id;

        $candidatas = Habitacion::query()
            ->select(['id', 'categoria_id', 'ubicacion_id', 'estado', 'reservable_id', 'nombre'])
            ->with('reservable')
            ->where('categoria_id', $categoriaId)
            ->where('ubicacion_id', $ubicacionId)
            ->whereNull('deleted_at')
            ->where('estado', '!=', EstadoEspacio::Inactivo->value)
            ->where(function (Builder $query) use ($totalPersonas): void {
                $query->whereDoesntHave('detalle')
                    ->orWhereHas('detalle', function (Builder $detalleQuery) use ($totalPersonas): void {
                        $detalleQuery
                            ->where(function (Builder $sinCapacidad): void {
                                $sinCapacidad
                                    ->whereNull('capacidad_adultos')
                                    ->whereNull('capacidad_ninos');
                            })
                            ->orWhereRaw('(COALESCE(capacidad_adultos, 0) + COALESCE(capacidad_ninos, 0)) >= ?', [$totalPersonas]);
                    });
            })
            ->where(function (Builder $query) use ($checkIn, $salida): void {
                $query->whereNull('reservable_id')
                    ->orWhereHas('reservable', function (Builder $reservableQuery) use ($checkIn, $salida): void {
                        $reservableQuery
                            ->where('tipo', TipoRecursoReservable::HABITACION)
                            ->where('estado', EstadoRecursoReservable::ACTIVO)
                            ->whereDoesntHave('detalles', function (Builder $detalleQuery) use ($checkIn, $salida): void {
                                $detalleQuery
                                    ->whereNull('deleted_at')
                                    ->whereIn('estado', [
                                        EstadoReservaDetalle::PENDIENTE,
                                        EstadoReservaDetalle::CONFIRMADO,
                                        EstadoReservaDetalle::EN_USO,
                                        EstadoReservaDetalle::REPROGRAMADO,
                                    ])
                                    ->where('fecha_inicio', '<', $salida)
                                    ->where('fecha_fin', '>', $checkIn)
                                    ->where(function (Builder $holdQuery): void {
                                        $holdQuery->whereNull('hold_expires_at')
                                            ->orWhere('hold_expires_at', '>', DB::raw('CURRENT_TIMESTAMP'));
                                    });
                            });
                    });
            })
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$habitacionSolicitadaId])
            ->orderBy('nombre')
            ->get();

        return [
            'categoriaId' => $categoriaId,
            'ubicacionId' => $ubicacionId,
            'candidatas' => $candidatas,
        ];
    }
}
