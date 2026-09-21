<?php

declare(strict_types=1);

namespace App\Http\Controllers\Financiero;

use App\Http\Controllers\ReporteController;
use App\Interactors\Reportes\Financiero\GenerarReporteFinanciero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteFinancieroController extends ReporteController
{
    public function cuentasCobrarPdf(Request $request, GenerarReporteFinanciero $reporte): Response|StreamedResponse|JsonResponse
    {
        $fechaInicio = $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d'));
        $formato = $this->textoRequest($request, 'formato_pagina');

        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'formato_pagina' => $formato];

        return $this->manejarReporte(
            $request,
            'HTB-FIN-001',
            $params,
            fn () => $reporte->cuentasCobrarPdf($fechaInicio, $fechaFin, $formato),
        );
    }

    public function facturacionVentasPdf(Request $request, GenerarReporteFinanciero $reporte): Response|StreamedResponse|JsonResponse
    {
        $fechaInicio = $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d'));
        $formato = $this->textoRequest($request, 'formato_pagina');

        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'formato_pagina' => $formato];

        return $this->manejarReporte(
            $request,
            'HTB-FIN-002',
            $params,
            fn () => $reporte->facturacionVentasPdf($fechaInicio, $fechaFin, $formato),
        );
    }

    public function resumenEjecutivoPdf(Request $request, GenerarReporteFinanciero $reporte): Response
    {
        $fechaInicio = $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d'));
        $formato = $this->textoRequest($request, 'formato_pagina');

        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'formato_pagina' => $formato];

        // El resumen ejecutivo es siempre pequeño (KPIs calculados, no filas)
        return $reporte->resumenEjecutivoPdf($fechaInicio, $fechaFin, $formato);
    }
}
