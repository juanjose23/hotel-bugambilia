<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Reservas\Reserva;

final class ResumenEjecutivoQuery
{
    /**
     * @return array{
     *   totalIngresosReservas: float,
     *   totalRecaudado: float,
     *   totalCuentasPorCobrar: float,
     *   cantidadReservas: int,
     *   totalFacturadoFiscal: float,
     * }
     */
    public function paraRango(string $fechaInicio, string $fechaFin): array
    {
        $totalIngresos = (float) Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total');
        $totalRecaudado = (float) Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total_pagado');
        $totalFacturadoFiscal = (float) Factura::whereBetween('fecha_emision', [$fechaInicio, $fechaFin])->sum('total');
        $cantidadReservas = Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin])->count();
        $totalCuentasPorCobrar = max(0.0, $totalIngresos - $totalRecaudado);

        return [
            'totalIngresosReservas' => $totalIngresos,
            'totalRecaudado' => $totalRecaudado,
            'totalCuentasPorCobrar' => $totalCuentasPorCobrar,
            'cantidadReservas' => $cantidadReservas,
            'totalFacturadoFiscal' => $totalFacturadoFiscal,
        ];
    }
}
