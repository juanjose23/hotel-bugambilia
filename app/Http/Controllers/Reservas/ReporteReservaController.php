<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reservas;

use App\Http\Controllers\Controller;
use App\Interactors\Reportes\Reservas\GenerarReporteReserva;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ReporteReservaController extends Controller
{
    public function ocupacionPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->ocupacionPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            estado: $this->textoRequest($request, 'estado'),
        );
    }

    public function ventasIngresosPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->ventasIngresosPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            tipoPago: $this->textoRequest($request, 'tipo_pago'),
        );
    }

    public function reservasEstadoPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->reservasEstadoPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            estado: $this->textoRequest($request, 'estado'),
        );
    }

    public function huespedesPdf(GenerarReporteReserva $reporte): Response
    {
        return $reporte->huespedesPdf();
    }

    public function rendimientoHabitacionesPdf(GenerarReporteReserva $reporte): Response
    {
        return $reporte->rendimientoHabitacionesPdf();
    }

    private function fechaRequest(Request $request, string $campo, string $porDefecto): string
    {
        $valor = $request->input($campo);

        return is_string($valor) && $valor !== '' ? $valor : $porDefecto;
    }

    private function textoRequest(Request $request, string $campo): ?string
    {
        $valor = $request->input($campo);

        return is_string($valor) && $valor !== '' ? $valor : null;
    }
}
