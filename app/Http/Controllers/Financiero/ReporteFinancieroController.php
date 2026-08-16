<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financiero;

use App\Http\Controllers\Controller;
use App\Interactors\Reportes\Financiero\GenerarReporteFinanciero;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ReporteFinancieroController extends Controller
{
    public function cuentasCobrarPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        return $reporte->cuentasCobrarPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
        );
    }

    public function facturacionVentasPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        return $reporte->facturacionVentasPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
        );
    }

    public function resumenEjecutivoPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        return $reporte->resumenEjecutivoPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
        );
    }

    private function fechaRequest(Request $request, string $campo, string $porDefecto): string
    {
        $valor = $request->input($campo);

        return is_string($valor) && $valor !== '' ? $valor : $porDefecto;
    }
}
