<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Reservas\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class ObtenerCalendarioReservasQuery
{
    /**
     * @return array{
     *     days: array<int, int|null>,
     *     nombreMes: string,
     *     year: int,
     *     month: int,
     *     categorias_habitacion: array<int, string>,
     *     reservasPorDia: Collection<int, Collection<int, array{id: int, codigo: string, cliente: string, telefono: string, tipo: string, estado: string, estado_enum: int, estado_color: string, fecha_check_in: string, fecha_check_out: string, habitacion_id: int|null, espacio_id: int|null, recurso_nombre: string, total: float}>>,
     *     totalReservas: int,
     *     totalMonto: float
     * }
     */
    public function ejecutar(
        int $month,
        int $year,
        string $tipoRecurso = 'todos', // 'habitaciones', 'espacios', 'todos'
        ?int $estadoId = null,
        ?string $categoria = null,
        ?string $buscar = null,
    ): array {
        $firstDayOfMonth = Carbon::now()->setDate($year, $month, 1)->startOfDay();
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeekIso;

        $days = [];
        for ($i = 1; $i < $firstDayOfWeek; $i++) {
            $days[] = null;
        }
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[] = $day;
        }

        $inicioMes = $firstDayOfMonth->copy()->startOfMonth();
        $finMes = $firstDayOfMonth->copy()->endOfMonth();

        /** @var array<int, string> $categoriasHabitacion */
        $categoriasHabitacion = Catalogo::query()
            ->orderBy('nombre')
            ->pluck('nombre')
            ->toArray();

        // Query de Reservaciones
        $reservaQuery = Reserva::query()
            ->where(function ($q) use ($inicioMes, $finMes) {
                $q->whereBetween('fecha_check_in', [$inicioMes->format('Y-m-d'), $finMes->format('Y-m-d')])
                    ->orWhereBetween('fecha_check_out', [$inicioMes->format('Y-m-d'), $finMes->format('Y-m-d')])
                    ->orWhere(function ($sub) use ($inicioMes, $finMes) {
                        $sub->where('fecha_check_in', '<=', $inicioMes->format('Y-m-d'))
                            ->where('fecha_check_out', '>=', $finMes->format('Y-m-d'));
                    });
            })
            ->whereNull('deleted_at');

        if ($tipoRecurso === 'habitaciones') {
            $reservaQuery->whereNotNull('habitacion_id');
        } elseif ($tipoRecurso === 'espacios') {
            $reservaQuery->whereNotNull('espacio_id');
        }

        if ($estadoId !== null && $estadoId > 0) {
            $reservaQuery->where('estado', $estadoId);
        }

        if ($categoria !== null && trim($categoria) !== '') {
            $reservaQuery->whereHas('habitacion.categoria', fn ($q) => $q->where('nombre', trim($categoria)));
        }

        if ($buscar !== null && trim($buscar) !== '') {
            $term = '%'.trim($buscar).'%';
            $reservaQuery->where(function ($q) use ($term) {
                $q->where('nombre_cliente', 'like', $term)
                    ->orWhere('codigo_reserva', 'like', $term)
                    ->orWhere('telefono_cliente', 'like', $term);
            });
        }

        $reservas = $reservaQuery->with(['habitacion.categoria', 'espacio'])->get();

        $totalReservas = $reservas->count();
        $sumTotal = $reservas->sum('total');

        $totalMonto = is_numeric($sumTotal) ? (float) $sumTotal : 0.0;
        /** @var Collection<int, array{id: int, codigo: string, cliente: string, telefono: string, tipo: string, estado: string, estado_enum: int, estado_color: string, fecha_check_in: string, fecha_check_out: string, habitacion_id: int|null, espacio_id: int|null, recurso_nombre: string, total: float}> $formatted */
        $formatted = $reservas->map(function (Reserva $r): array {
            $recurso = $r->habitacion !== null
                ? 'Hab. '.$r->habitacion->numero
                : ($r->espacio !== null ? $r->espacio->nombre : 'Reserva General');

            $color = match ($r->estado) {
                EstadoReserva::CONFIRMADA => 'emerald',
                EstadoReserva::CHECKED_IN, EstadoReserva::CHECKED_OUT => 'sky',
                EstadoReserva::CANCELADA => 'rose',
                default => 'amber',
            };

            $inStr = Carbon::parse($r->fecha_check_in)->format('Y-m-d');
            $outStr = Carbon::parse($r->fecha_check_out)->format('Y-m-d');

            return [
                'id' => $r->id,
                'codigo' => (string) $r->codigo_reserva,
                'cliente' => (string) ($r->nombre_cliente ?? 'Cliente'),
                'telefono' => (string) ($r->telefono_cliente ?? ''),
                'tipo' => $r->tipo_reserva->getLabel(),
                'estado' => $r->estado->getLabel(),
                'estado_enum' => $r->estado->value,
                'estado_color' => $color,
                'fecha_check_in' => $inStr,
                'fecha_check_out' => $outStr,
                'habitacion_id' => $r->habitacion_id,
                'espacio_id' => $r->espacio_id,
                'recurso_nombre' => $recurso,
                'total' => (float) $r->total,
                'day_check_in' => (int) Carbon::parse($r->fecha_check_in)->day,
            ];
        });

        // Agrupar reservas por día de Check-in dentro del mes
        /** @var Collection<int, Collection<int, array{id: int, codigo: string, cliente: string, telefono: string, tipo: string, estado: string, estado_enum: int, estado_color: string, fecha_check_in: string, fecha_check_out: string, habitacion_id: int|null, espacio_id: int|null, recurso_nombre: string, total: float}>> $reservasPorDia */
        $reservasPorDia = $formatted->groupBy('day_check_in');

        $firstDayOfMonth->locale('es');
        $nombreMes = ucfirst((string) $firstDayOfMonth->translatedFormat('F'));

        return [
            'days' => $days,
            'nombreMes' => $nombreMes,
            'year' => $year,
            'month' => $month,
            'categorias_habitacion' => $categoriasHabitacion,
            'reservasPorDia' => $reservasPorDia,
            'totalReservas' => $totalReservas,
            'totalMonto' => $totalMonto,
        ];
    }
}
