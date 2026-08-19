<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas\Reportes;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;

final class ObtenerMetricasReporteReservasQuery
{
    /**
     * @return array{
     *     porcentajeOcupacion: int,
     *     checkinsHoy: int,
     *     facturacionMes: float,
     *     pagosPendientes: int
     * }
     */
    public function ejecutar(): array
    {
        $totalHabitaciones = Habitacion::count();
        $reservasActivas = Reserva::whereDate('fecha_check_in', '<=', now()->toDateString())
            ->whereDate('fecha_check_out', '>=', now()->toDateString())
            ->whereIn('estado', [EstadoReserva::CONFIRMADA, EstadoReserva::CHECKED_IN])
            ->count();
        $porcentajeOcupacion = $totalHabitaciones > 0 ? (int) round(($reservasActivas / $totalHabitaciones) * 100, 0) : 0;

        return [
            'porcentajeOcupacion' => $porcentajeOcupacion,
            'checkinsHoy' => Reserva::whereDate('fecha_check_in', now()->toDateString())->count(),
            'facturacionMes' => (float) Reserva::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'pagosPendientes' => Reserva::where('saldo', '>', 0)
                ->whereIn('estado', [EstadoReserva::CONFIRMADA, EstadoReserva::PENDIENTE, EstadoReserva::CHECKED_IN])
                ->count(),
        ];
    }
}
