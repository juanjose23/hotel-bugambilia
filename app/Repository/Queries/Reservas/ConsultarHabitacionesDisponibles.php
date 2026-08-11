<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\BusinessLogic\Reservas\Data\ConsultarDisponibilidadHabitacionData;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Repository\Models\Reservas\RecursoReservable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsultarHabitacionesDisponibles
{
    /**
     * @return Collection<int, RecursoReservable>
     */
    public function ejecutar(ConsultarDisponibilidadHabitacionData $data, ?int $excluirDetalleId = null): Collection
    {
        return $this->obtenerRecursosDisponibles(
            fechaCheckIn: $data->fechaCheckIn,
            fechaCheckOut: $data->fechaCheckOut,
            adultos: $data->adultos,
            ninos: $data->ninos,
            categoriaHabitacionId: $data->categoriaHabitacionId,
            excluirDetalleId: $excluirDetalleId,
        );
    }

    /**
     * @return Collection<int, RecursoReservable>
     */
    public function obtenerRecursosDisponibles(
        CarbonInterface $fechaCheckIn,
        CarbonInterface $fechaCheckOut,
        int $adultos = 1,
        int $ninos = 0,
        ?int $categoriaHabitacionId = null,
        ?int $excluirDetalleId = null,
    ): Collection {
        $totalPersonas = $adultos + $ninos;

        return RecursoReservable::query()
            ->where('tipo', TipoRecursoReservable::HABITACION)
            ->where('estado', EstadoRecursoReservable::ACTIVO)
            ->where('capacidad', '>=', $totalPersonas)
            ->whereHas('habitacion', function (Builder $query) use ($categoriaHabitacionId): void {
                $query->whereNull('deleted_at');

                if ($categoriaHabitacionId !== null) {
                    $query->where('categoria_id', $categoriaHabitacionId);
                }
            })
            ->whereDoesntHave('detalles', function (Builder $query) use ($fechaCheckIn, $fechaCheckOut, $excluirDetalleId): void {
                $query->whereNull('deleted_at')
                    ->whereIn('estado', [
                        EstadoReservaDetalle::PENDIENTE,
                        EstadoReservaDetalle::CONFIRMADO,
                        EstadoReservaDetalle::EN_USO,
                        EstadoReservaDetalle::REPROGRAMADO,
                    ])
                    ->where('fecha_inicio', '<', $fechaCheckOut)
                    ->where('fecha_fin', '>', $fechaCheckIn)
                    ->where(function (Builder $subQuery): void {
                        $subQuery->whereNull('hold_expires_at')
                            ->orWhere('hold_expires_at', '>', DB::raw('CURRENT_TIMESTAMP'));
                    });

                if ($excluirDetalleId !== null) {
                    $query->where('id', '!=', $excluirDetalleId);
                }
            })
            ->with(['habitacion.categoria', 'habitacion.ubicacion'])
            ->get();
    }
}
