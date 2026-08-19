<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financiero;

use App\Http\Controllers\ReporteController;
use App\Interactors\Reportes\Financiero\GenerarReporteFinanciero;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ReporteFinancieroController extends ReporteController
{
    public function cuentasCobrarPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        return $reporte->cuentasCobrarPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }

    public function facturacionVentasPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        return $reporte->facturacionVentasPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }

    public function resumenEjecutivoPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        return $reporte->resumenEjecutivoPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }
}
