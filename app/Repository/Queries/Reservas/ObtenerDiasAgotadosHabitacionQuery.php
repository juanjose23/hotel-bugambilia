<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ObtenerDiasAgotadosHabitacionQuery
{
    /**
     * @return array{
     *     total_habitaciones: int,
     *     dias_agotados: array<int, string>,
     *     ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>
     * }
     */
    public function porCategoria(
        int $categoriaId,
        CarbonInterface $inicio,
        CarbonInterface $fin,
        ?int $reservaExcluidaId = null,
        ?int $ubicacionId = null,
        int $adultos = 1,
        int $ninos = 0,
    ): array {
        $inicioRango = CarbonImmutable::instance($inicio)->startOfDay();
        $finRango = CarbonImmutable::instance($fin)->startOfDay();
        $totalPersonas = $adultos + $ninos;

        if ($finRango->lessThanOrEqualTo($inicioRango)) {
            return [
                'total_habitaciones' => 0,
                'dias_agotados' => [],
                'ocupacion_por_dia' => [],
            ];
        }

        /** @var Collection<int, int> $habitacionIds */
        $habitacionIds = Habitacion::query()
            ->where('categoria_id', $categoriaId)
            ->when($ubicacionId !== null, fn (Builder $query): Builder => $query->where('ubicacion_id', $ubicacionId))
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
            ->pluck('id')
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0);

        return $this->calcularOcupacion($habitacionIds, $inicioRango, $finRango, $reservaExcluidaId);
    }

    /**
     * @return array{
     *     total_habitaciones: int,
     *     dias_agotados: array<int, string>,
     *     ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>
     * }
     */
    public function porHabitacion(
        int $habitacionId,
        CarbonInterface $inicio,
        CarbonInterface $fin,
        ?int $reservaExcluidaId = null,
    ): array {
        $inicioRango = CarbonImmutable::instance($inicio)->startOfDay();
        $finRango = CarbonImmutable::instance($fin)->startOfDay();

        if ($finRango->lessThanOrEqualTo($inicioRango)) {
            return [
                'total_habitaciones' => 0,
                'dias_agotados' => [],
                'ocupacion_por_dia' => [],
            ];
        }

        $habitacionExiste = Habitacion::query()
            ->whereKey($habitacionId)
            ->whereNull('deleted_at')
            ->where('estado', '!=', EstadoEspacio::Inactivo->value)
            ->exists();

        if (! $habitacionExiste) {
            return [
                'total_habitaciones' => 0,
                'dias_agotados' => [],
                'ocupacion_por_dia' => [],
            ];
        }

        return $this->calcularOcupacion(collect([$habitacionId]), $inicioRango, $finRango, $reservaExcluidaId);
    }

    /**
     * @param  Collection<int, int>  $habitacionIds
     * @return array{
     *     total_habitaciones: int,
     *     dias_agotados: array<int, string>,
     *     ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>
     * }
     */
    private function calcularOcupacion(
        Collection $habitacionIds,
        CarbonImmutable $inicioRango,
        CarbonImmutable $finRango,
        ?int $reservaExcluidaId,
    ): array {
        $totalHabitaciones = $habitacionIds->count();

        if ($totalHabitaciones === 0) {
            return [
                'total_habitaciones' => 0,
                'dias_agotados' => [],
                'ocupacion_por_dia' => [],
            ];
        }

        /** @var array<string, array<int, int>> $ocupadasPorDia */
        $ocupadasPorDia = [];

        $this->agregarOcupacionPorDetalles(
            ocupadasPorDia: $ocupadasPorDia,
            habitacionIds: $habitacionIds,
            inicioRango: $inicioRango,
            finRango: $finRango,
            reservaExcluidaId: $reservaExcluidaId,
        );

        $this->agregarOcupacionPorReservasLegacy(
            ocupadasPorDia: $ocupadasPorDia,
            habitacionIds: $habitacionIds,
            inicioRango: $inicioRango,
            finRango: $finRango,
            reservaExcluidaId: $reservaExcluidaId,
        );

        $diasAgotados = [];
        $ocupacionPorDia = [];

        for ($fecha = $inicioRango; $fecha->lessThan($finRango); $fecha = $fecha->addDay()) {
            $key = $fecha->format('Y-m-d');
            $ocupadas = count(array_unique($ocupadasPorDia[$key] ?? []));
            $disponibles = max(0, $totalHabitaciones - $ocupadas);
            $agotado = $ocupadas >= $totalHabitaciones;

            if ($agotado) {
                $diasAgotados[] = $key;
            }

            $ocupacionPorDia[$key] = [
                'ocupadas' => $ocupadas,
                'total' => $totalHabitaciones,
                'disponibles' => $disponibles,
                'agotado' => $agotado,
            ];
        }

        return [
            'total_habitaciones' => $totalHabitaciones,
            'dias_agotados' => $diasAgotados,
            'ocupacion_por_dia' => $ocupacionPorDia,
        ];
    }

    /**
     * @return array{
     *     disponible: bool,
     *     noches: int,
     *     total_habitaciones: int,
     *     disponibilidad_minima: int,
     *     dias_sin_disponibilidad: array<int, string>,
     *     recomendaciones: array<int, array{fecha_check_in: string, fecha_check_out: string, noches: int, disponibles_minimos: int}>
     * }
     */
    public function recomendarPorCategoria(
        int $categoriaId,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $adultos = 1,
        int $ninos = 0,
        ?int $reservaExcluidaId = null,
        ?int $ubicacionId = null,
        int $diasBusqueda = 45,
        int $limiteRecomendaciones = 3,
    ): array {
        $inicio = CarbonImmutable::instance($checkIn)->startOfDay();
        $fin = CarbonImmutable::instance($checkOut)->startOfDay();
        $noches = max(1, (int) $inicio->diffInDays($fin));

        $inicioBusqueda = $inicio->subDays(min(14, $diasBusqueda))->startOfDay();
        $finBusqueda = $fin->addDays($diasBusqueda)->startOfDay();

        $disponibilidad = $this->porCategoriaConCapacidad(
            categoriaId: $categoriaId,
            inicio: $inicioBusqueda,
            fin: $finBusqueda,
            adultos: $adultos,
            ninos: $ninos,
            reservaExcluidaId: $reservaExcluidaId,
            ubicacionId: $ubicacionId,
        );

        /** @var array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}> $ocupacion */
        $ocupacion = $disponibilidad['ocupacion_por_dia'];
        $diasSolicitados = $this->diasDelRango($inicio, $fin);
        $disponibilidadSolicitada = array_map(
            fn (string $dia): int => $ocupacion[$dia]['disponibles'] ?? 0,
            $diasSolicitados,
        );
        $disponibilidadMinima = $disponibilidadSolicitada === [] ? 0 : min($disponibilidadSolicitada);
        $diasSinDisponibilidad = array_values(array_filter(
            $diasSolicitados,
            fn (string $dia): bool => ($ocupacion[$dia]['disponibles'] ?? 0) <= 0,
        ));

        return [
            'disponible' => $diasSinDisponibilidad === [],
            'noches' => $noches,
            'total_habitaciones' => $disponibilidad['total_habitaciones'],
            'disponibilidad_minima' => $disponibilidadMinima,
            'dias_sin_disponibilidad' => $diasSinDisponibilidad,
            'recomendaciones' => $diasSinDisponibilidad === []
                ? []
                : $this->recomendarRangosContinuos($ocupacion, $inicioBusqueda, $finBusqueda, $noches, $inicio, $limiteRecomendaciones),
        ];
    }

    /**
     * @return array{
     *     total_habitaciones: int,
     *     dias_agotados: array<int, string>,
     *     ocupacion_por_dia: array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>
     * }
     */
    private function porCategoriaConCapacidad(
        int $categoriaId,
        CarbonInterface $inicio,
        CarbonInterface $fin,
        int $adultos,
        int $ninos,
        ?int $reservaExcluidaId,
        ?int $ubicacionId,
    ): array {
        $inicioRango = CarbonImmutable::instance($inicio)->startOfDay();
        $finRango = CarbonImmutable::instance($fin)->startOfDay();
        $totalPersonas = $adultos + $ninos;

        if ($finRango->lessThanOrEqualTo($inicioRango)) {
            return ['total_habitaciones' => 0, 'dias_agotados' => [], 'ocupacion_por_dia' => []];
        }

        /** @var Collection<int, int> $habitacionIds */
        $habitacionIds = Habitacion::query()
            ->where('categoria_id', $categoriaId)
            ->when($ubicacionId !== null, fn (Builder $query): Builder => $query->where('ubicacion_id', $ubicacionId))
            ->whereNull('deleted_at')
            ->where('estado', '!=', EstadoEspacio::Inactivo->value)
            ->where(function (Builder $query) use ($totalPersonas): void {
                $query->whereDoesntHave('detalle')
                    ->orWhereHas('detalle', function (Builder $detalleQuery) use ($totalPersonas): void {
                        $detalleQuery
                            ->where(function (Builder $sinCapacidadDefinida): void {
                                $sinCapacidadDefinida
                                    ->whereNull('capacidad_adultos')
                                    ->whereNull('capacidad_ninos');
                            })
                            ->orWhereRaw('(COALESCE(capacidad_adultos, 0) + COALESCE(capacidad_ninos, 0)) >= ?', [$totalPersonas]);
                    });
            })
            ->pluck('id')
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0);

        return $this->calcularOcupacion($habitacionIds, $inicioRango, $finRango, $reservaExcluidaId);
    }

    /**
     * @param  Collection<int, int>  $habitacionIds
     * @param  array<string, array<int, int>>  $ocupadasPorDia
     */
    private function agregarOcupacionPorDetalles(
        array &$ocupadasPorDia,
        Collection $habitacionIds,
        CarbonImmutable $inicioRango,
        CarbonImmutable $finRango,
        ?int $reservaExcluidaId,
    ): void {
        ReservaDetalle::query()
            ->select(['id', 'reserva_id', 'reservable_id', 'fecha_inicio', 'fecha_fin'])
            ->with(['reservable.habitacion:id,reservable_id'])
            ->whereNull('deleted_at')
            ->whereIn('estado', [
                EstadoReservaDetalle::PENDIENTE,
                EstadoReservaDetalle::CONFIRMADO,
                EstadoReservaDetalle::EN_USO,
                EstadoReservaDetalle::REPROGRAMADO,
            ])
            ->where('fecha_inicio', '<', $finRango)
            ->where(function (Builder $query) use ($inicioRango): void {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>', $inicioRango);
            })
            ->where(function (Builder $query): void {
                $query->whereNull('hold_expires_at')
                    ->orWhere('hold_expires_at', '>', DB::raw('CURRENT_TIMESTAMP'));
            })
            ->when($reservaExcluidaId !== null, fn (Builder $query): Builder => $query->where('reserva_id', '!=', $reservaExcluidaId))
            ->whereHas('reservable.habitacion', fn (Builder $query): Builder => $query->whereIn('id', $habitacionIds))
            ->get()
            ->each(function (ReservaDetalle $detalle) use (&$ocupadasPorDia, $inicioRango, $finRango): void {
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
                    inicio: $inicioDetalle->max($inicioRango),
                    fin: $finDetalle->min($finRango),
                );
            });
    }

    /**
     * @param  Collection<int, int>  $habitacionIds
     * @param  array<string, array<int, int>>  $ocupadasPorDia
     */
    private function agregarOcupacionPorReservasLegacy(
        array &$ocupadasPorDia,
        Collection $habitacionIds,
        CarbonImmutable $inicioRango,
        CarbonImmutable $finRango,
        ?int $reservaExcluidaId,
    ): void {
        Reserva::query()
            ->select(['id', 'habitacion_id', 'fecha_check_in', 'fecha_check_out'])
            ->whereNull('deleted_at')
            ->whereIn('habitacion_id', $habitacionIds)
            ->whereNotIn('estado', [EstadoReserva::CANCELADA, EstadoReserva::CHECKED_OUT, EstadoReserva::NO_SHOW])
            ->where('fecha_check_in', '<', $finRango->format('Y-m-d'))
            ->where(function (Builder $query) use ($inicioRango): void {
                $query->whereNull('fecha_check_out')
                    ->orWhere('fecha_check_out', '>', $inicioRango->format('Y-m-d'));
            })
            ->when($reservaExcluidaId !== null, fn (Builder $query): Builder => $query->whereKeyNot($reservaExcluidaId))
            ->whereDoesntHave('detalles')
            ->get()
            ->each(function (Reserva $reserva) use (&$ocupadasPorDia, $inicioRango, $finRango): void {
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
                    inicio: $inicioReserva->max($inicioRango),
                    fin: $finReserva->min($finRango),
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

    /**
     * @return array<int, string>
     */
    private function diasDelRango(CarbonImmutable $inicio, CarbonImmutable $fin): array
    {
        $dias = [];

        for ($fecha = $inicio; $fecha->lessThan($fin); $fecha = $fecha->addDay()) {
            $dias[] = $fecha->format('Y-m-d');
        }

        return $dias;
    }

    /**
     * @param  array<string, array{ocupadas: int, total: int, disponibles: int, agotado: bool}>  $ocupacion
     * @return array<int, array{fecha_check_in: string, fecha_check_out: string, noches: int, disponibles_minimos: int}>
     */
    private function recomendarRangosContinuos(
        array $ocupacion,
        CarbonImmutable $inicioBusqueda,
        CarbonImmutable $finBusqueda,
        int $noches,
        CarbonImmutable $inicioSolicitado,
        int $limite,
    ): array {
        $recomendaciones = [];

        for ($entrada = $inicioBusqueda; $entrada->addDays($noches)->lessThanOrEqualTo($finBusqueda); $entrada = $entrada->addDay()) {
            $salida = $entrada->addDays($noches);
            $dias = $this->diasDelRango($entrada, $salida);
            $disponiblesPorDia = array_map(
                fn (string $dia): int => $ocupacion[$dia]['disponibles'] ?? 0,
                $dias,
            );

            if ($disponiblesPorDia === [] || min($disponiblesPorDia) <= 0) {
                continue;
            }

            $recomendaciones[] = [
                'fecha_check_in' => $entrada->format('Y-m-d'),
                'fecha_check_out' => $salida->format('Y-m-d'),
                'noches' => $noches,
                'disponibles_minimos' => min($disponiblesPorDia),
                'distancia' => abs($entrada->diffInDays($inicioSolicitado, false)),
            ];
        }

        usort(
            $recomendaciones,
            fn (array $a, array $b): int => ($a['distancia'] <=> $b['distancia'])
                ?: strcmp((string) $a['fecha_check_in'], (string) $b['fecha_check_in'])
        );

        return array_map(
            fn (array $recomendacion): array => [
                'fecha_check_in' => (string) $recomendacion['fecha_check_in'],
                'fecha_check_out' => (string) $recomendacion['fecha_check_out'],
                'noches' => (int) $recomendacion['noches'],
                'disponibles_minimos' => (int) $recomendacion['disponibles_minimos'],
            ],
            array_slice($recomendaciones, 0, $limite)
        );
    }
}
