<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reservas;

use App\Http\Controllers\ReporteController;
use App\Interactors\Reportes\Reservas\GenerarReporteReserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReporteReservaController extends ReporteController
{
    public function ocupacionPdf(Request $request, GenerarReporteReserva $reporte): Response|StreamedResponse|JsonResponse
    {
        $fechaInicio = $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d'));
        $estado = $this->textoRequest($request, 'estado');
        $formato = $this->textoRequest($request, 'formato_pagina');

        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'estado' => $estado];

        return $this->manejarReporte(
            $request,
            'HTB-RES-001',
            $params,
            fn () => $reporte->ocupacionPdf($fechaInicio, $fechaFin, $estado, $formato),
        );
    }

    public function ventasIngresosPdf(Request $request, GenerarReporteReserva $reporte): Response|StreamedResponse|JsonResponse
    {
        $fechaInicio = $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d'));
        $tipoPago = $this->textoRequest($request, 'tipo_pago');
        $formato = $this->textoRequest($request, 'formato_pagina');

        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];

        return $this->manejarReporte(
            $request,
            'HTB-RES-002',
            $params,
            fn () => $reporte->ventasIngresosPdf($fechaInicio, $fechaFin, $tipoPago, $formato),
        );
    }

    public function reservasEstadoPdf(Request $request, GenerarReporteReserva $reporte): Response|StreamedResponse|JsonResponse
    {
        $fechaInicio = $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d'));
        $estado = $this->textoRequest($request, 'estado');
        $formato = $this->textoRequest($request, 'formato_pagina');

        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'estado' => $estado];

        return $this->manejarReporte(
            $request,
            'HTB-RES-003',
            $params,
            fn () => $reporte->reservasEstadoPdf($fechaInicio, $fechaFin, $estado, $formato),
        );
    }

    public function huespedesPdf(Request $request, GenerarReporteReserva $reporte): Response|StreamedResponse|JsonResponse
    {
        $formato = $this->textoRequest($request, 'formato_pagina');

        return $this->manejarReporte(
            $request,
            'HTB-RES-004',
            $request->all(),
            fn () => $reporte->huespedesPdf(formatoPagina: $formato),
        );
    }

    public function rendimientoHabitacionesPdf(Request $request, GenerarReporteReserva $reporte): Response|StreamedResponse|JsonResponse
    {
        $formato = $this->textoRequest($request, 'formato_pagina');

        return $this->manejarReporte(
            $request,
            'HTB-RES-005',
            $request->all(),
            fn () => $reporte->rendimientoHabitacionesPdf(formatoPagina: $formato),
        );
    }
}
