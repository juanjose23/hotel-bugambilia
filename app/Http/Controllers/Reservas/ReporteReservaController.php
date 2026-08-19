<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reservas;

use App\Http\Controllers\ReporteController;
use App\Interactors\Reportes\Reservas\GenerarReporteReserva;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ReporteReservaController extends ReporteController
{
    public function ocupacionPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->ocupacionPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            estado: $this->textoRequest($request, 'estado'),
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }

    public function ventasIngresosPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->ventasIngresosPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            tipoPago: $this->textoRequest($request, 'tipo_pago'),
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }

    public function reservasEstadoPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->reservasEstadoPdf(
            fechaInicio: $this->fechaRequest($request, 'fecha_inicio', now()->startOfMonth()->format('Y-m-d')),
            fechaFin: $this->fechaRequest($request, 'fecha_fin', now()->format('Y-m-d')),
            estado: $this->textoRequest($request, 'estado'),
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }

    public function huespedesPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->huespedesPdf(
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }

    public function rendimientoHabitacionesPdf(Request $request, GenerarReporteReserva $reporte): Response
    {
        return $reporte->rendimientoHabitacionesPdf(
            formatoPagina: $this->textoRequest($request, 'formato_pagina'),
        );
    }
}
