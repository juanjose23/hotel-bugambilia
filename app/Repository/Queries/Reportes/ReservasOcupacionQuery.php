<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class ReservasOcupacionQuery
{
    /**
     * @return Collection<int, Reserva>
     */
    public function paraOcupacion(string $fechaInicio, string $fechaFin, ?string $estado): Collection
    {
        return Reserva::with(['habitacion', 'cliente.persona'])
            ->whereNotNull('habitacion_id')
            ->whereDate('fecha_check_in', '>=', $fechaInicio)
            ->whereDate('fecha_check_in', '<=', $fechaFin)
            ->when($estado !== null && $estado !== '', fn ($query) => $query->where('estado', $estado))
            ->orderBy('fecha_check_in', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function paraEstados(string $fechaInicio, string $fechaFin, ?string $estado): Collection
    {
        $query = Reserva::with(['habitacion', 'cliente.persona'])
            ->whereDate('fecha_check_in', '>=', $fechaInicio)
            ->whereDate('fecha_check_in', '<=', $fechaFin);

        if ($estado !== null && $estado !== '') {
            $query->where('estado', $estado);
        }

        return $query->orderBy('estado')->orderBy('fecha_check_in', 'desc')->get();
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function paraVentas(string $fechaInicio, string $fechaFin, ?string $tipoPago): Collection
    {
        $query = Reserva::with(['cliente.persona'])
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin);

        if ($tipoPago !== null && $tipoPago !== '') {
            $query->where('tipo_pago_reserva', $tipoPago);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Ocupación de habitaciones por día dentro del rango [inicio, fin].
     *
     * Se alimenta de los detalles reservados (modelo multi-recurso) con respaldo
     * de reservas legacy (sin detalles) para no perder registros migrados.
     *
     * @return array<string, array{ocupadas: int, total: int, porcentaje: float}> Clave: fecha 'Y-m-d'
     */
    public function ocupacionPorDia(string $fechaInicio, string $fechaFin): array
    {
        $inicio = CarbonImmutable::parse($fechaInicio)->startOfDay();
        $fin = CarbonImmutable::parse($fechaFin)->startOfDay()->addDay();

        if ($fin->lessThanOrEqualTo($inicio)) {
            $fin = $inicio->addDay();
        }

        $totalHabitaciones = Habitacion::count();
        $ocupadasPorDia = [];

        $this->agregarOcupacionPorDetalles($ocupadasPorDia, $inicio, $fin);
        $this->agregarOcupacionPorReservasLegacy($ocupadasPorDia, $inicio, $fin);

        $porDia = [];
        for ($fecha = $inicio; $fecha->lessThan($fin); $fecha = $fecha->addDay()) {
            $clave = $fecha->format('Y-m-d');
            $ocupadas = count(array_unique($ocupadasPorDia[$clave] ?? []));
            $porDia[$clave] = [
                'ocupadas' => $ocupadas,
                'total' => $totalHabitaciones,
                'porcentaje' => $totalHabitaciones > 0 ? round(($ocupadas / $totalHabitaciones) * 100, 1) : 0.0,
            ];
        }

        return $porDia;
    }

    /**
     * @param  array<string, array<int, int>>  $ocupadasPorDia
     */
    private function agregarOcupacionPorDetalles(
        array &$ocupadasPorDia,
        CarbonImmutable $inicio,
        CarbonImmutable $fin,
    ): void {
        ReservaDetalle::query()
            ->select(['id', 'reservable_id', 'fecha_inicio', 'fecha_fin'])
            ->with(['reservable.habitacion:id,reservable_id'])
            ->whereNull('deleted_at')
            ->whereIn('estado', [
                EstadoReservaDetalle::PENDIENTE,
                EstadoReservaDetalle::CONFIRMADO,
                EstadoReservaDetalle::EN_USO,
                EstadoReservaDetalle::REPROGRAMADO,
            ])
            ->where('fecha_inicio', '<', $fin)
            ->where(function (Builder $query) use ($inicio): void {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>', $inicio);
            })
            ->where(function (Builder $query): void {
                $query->whereNull('hold_expires_at')
                    ->orWhere('hold_expires_at', '>', DB::raw('CURRENT_TIMESTAMP'));
            })
            ->whereHas('reservable.habitacion')
            ->get()
            ->each(function (ReservaDetalle $detalle) use (&$ocupadasPorDia, $inicio, $fin): void {
                $habitacionId = $detalle->reservable?->habitacion?->id;

                if (! is_int($habitacionId)) {
                    return;
                }

                $inicioDetalle = CarbonImmutable::instance($detalle->fecha_inicio);
                $finDetalle = $detalle->fecha_fin !== null
                    ? CarbonImmutable::instance($detalle->fecha_fin)
                    : $inicioDetalle->addDay();

                $this->marcarRangoOcupado(
                    ocupadasPorDia: $ocupadasPorDia,
                    habitacionId: $habitacionId,
                    inicio: $inicioDetalle->max($inicio),
                    fin: $finDetalle->min($fin),
                );
            });
    }

    /**
     * @param  array<string, array<int, int>>  $ocupadasPorDia
     */
    private function agregarOcupacionPorReservasLegacy(
        array &$ocupadasPorDia,
        CarbonImmutable $inicio,
        CarbonImmutable $fin,
    ): void {
        Reserva::query()
            ->select(['id', 'habitacion_id', 'fecha_check_in', 'fecha_check_out'])
            ->whereNull('deleted_at')
            ->whereNotNull('habitacion_id')
            ->whereNotIn('estado', [EstadoReserva::CANCELADA, EstadoReserva::CHECKED_OUT, EstadoReserva::NO_SHOW])
            ->where('fecha_check_in', '<', $fin->format('Y-m-d'))
            ->where(function (Builder $query) use ($inicio): void {
                $query->whereNull('fecha_check_out')
                    ->orWhere('fecha_check_out', '>', $inicio->format('Y-m-d'));
            })
            ->whereDoesntHave('detalles')
            ->get()
            ->each(function (Reserva $reserva) use (&$ocupadasPorDia, $inicio, $fin): void {
                if (! is_int($reserva->habitacion_id) || $reserva->fecha_check_in === null) {
                    return;
                }

                $inicioReserva = CarbonImmutable::parse($reserva->fecha_check_in);
                $finReserva = $reserva->fecha_check_out !== null
                    ? CarbonImmutable::parse($reserva->fecha_check_out)
                    : $inicioReserva->addDay();

                $this->marcarRangoOcupado(
                    ocupadasPorDia: $ocupadasPorDia,
                    habitacionId: $reserva->habitacion_id,
                    inicio: $inicioReserva->max($inicio),
                    fin: $finReserva->min($fin),
                );
            });
    }

    /**
     * @param  array<string, array<int, int>>  $ocupadasPorDia
     */
    private function marcarRangoOcupado(
        array &$ocupadasPorDia,
        int $habitacionId,
        CarbonImmutable $inicio,
        CarbonImmutable $fin,
    ): void {
        for ($fecha = $inicio->startOfDay(); $fecha->lessThan($fin->startOfDay()); $fecha = $fecha->addDay()) {
            $ocupadasPorDia[$fecha->format('Y-m-d')][] = $habitacionId;
        }
    }
}
