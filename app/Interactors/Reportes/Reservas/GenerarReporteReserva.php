<?php

declare(strict_types=1);

namespace App\Interactors\Reportes\Reservas;

use App\Repository\Queries\Reportes\HuespedesQuery;
use App\Repository\Queries\Reportes\RendimientoHabitacionesQuery;
use App\Repository\Queries\Reportes\ReservasOcupacionQuery;
use App\Support\HotelInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class GenerarReporteReserva
{
    public function __construct(
        private readonly ReservasOcupacionQuery $reservasOcupacion,
        private readonly HuespedesQuery $huespedes,
        private readonly RendimientoHabitacionesQuery $rendimientoHabitaciones,
    ) {}

    /**
     * @return array<string, mixed>
     */
    private function datosHotel(): array
    {
        return HotelInfo::getBaseData();
    }

    public function ocupacionPdf(string $fechaInicio, string $fechaFin, ?string $estado): Response
    {
        $reservas = $this->reservasOcupacion->paraOcupacion($fechaInicio, $fechaFin, $estado);

        return Pdf::loadView('pdf.reservas.reporte-ocupacion', [
            'titulo' => 'Reporte de Ocupación y Estadías',
            'codigo' => 'HTB-RES-001',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'reservas' => $reservas,
            'totalNoches' => $reservas->sum('noches'),
            'totalIngresos' => $reservas->sum('total'),
        ])->stream("Reporte_Ocupacion_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function ventasIngresosPdf(string $fechaInicio, string $fechaFin, ?string $tipoPago): Response
    {
        $reservas = $this->reservasOcupacion->paraVentas($fechaInicio, $fechaFin, $tipoPago);

        return Pdf::loadView('pdf.reservas.reporte-ventas', [
            'titulo' => 'Ventas e Ingresos por Canal de Pago',
            'codigo' => 'HTB-RES-002',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'reservas' => $reservas,
            'totalVentas' => $reservas->sum('total'),
            'totalPagado' => $reservas->sum('total_pagado'),
            'totalSaldo' => $reservas->sum('saldo'),
        ])->stream("Reporte_Ventas_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function reservasEstadoPdf(string $fechaInicio, string $fechaFin, ?string $estado): Response
    {
        $reservas = $this->reservasOcupacion->paraEstados($fechaInicio, $fechaFin, $estado);

        return Pdf::loadView('pdf.reservas.reporte-estados', [
            'titulo' => 'Reservas Agrupadas por Estado',
            'codigo' => 'HTB-RES-003',
            'datosHotel' => $this->datosHotel(),
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'reservas' => $reservas,
        ])->stream("Reporte_Reservas_Estado_{$fechaInicio}_{$fechaFin}.pdf");
    }

    public function huespedesPdf(): Response
    {
        return Pdf::loadView('pdf.reservas.reporte-huespedes', [
            'titulo' => 'Listado y Fichas de Huéspedes',
            'codigo' => 'HTB-RES-004',
            'datosHotel' => $this->datosHotel(),
            'clientes' => $this->huespedes->todosConReservas(),
        ])->stream('Reporte_Huespedes.pdf');
    }

    public function rendimientoHabitacionesPdf(): Response
    {
        return Pdf::loadView('pdf.reservas.reporte-rendimiento-habitaciones', [
            'titulo' => 'Rendimiento por Categoría de Habitación',
            'codigo' => 'HTB-RES-005',
            'datosHotel' => $this->datosHotel(),
            'categorias' => $this->rendimientoHabitaciones->categorias(),
        ])->stream('Reporte_Rendimiento_Habitaciones.pdf');
    }
}
