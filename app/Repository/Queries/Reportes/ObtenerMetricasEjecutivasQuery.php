<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Reservas\Reserva;

final class ObtenerMetricasEjecutivasQuery
{
    /**
     * @return array{
     *     totalIngresosReservas: float,
     *     totalRecaudado: float,
     *     cantidadReservas: int,
     *     totalCuentasPorCobrar: float,
     *     totalFacturadoFiscal: float
     * }
     */
    public function ejecutar(string $fechaInicio, string $fechaFin): array
    {
        $queryReserva = Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin]);
        $totalIngresosReservas = (float) $queryReserva->sum('total');
        $totalRecaudado = (float) $queryReserva->sum('total_pagado');
        $cantidadReservas = $queryReserva->count();

        $totalCuentasPorCobrar = (float) (Reserva::sum('saldo') + Cuenta::sum('saldo'));

        $totalFacturadoFiscal = (float) Factura::whereBetween('fecha_emision', [$fechaInicio, $fechaFin])->sum('total');

        return [
            'totalIngresosReservas' => $totalIngresosReservas,
            'totalRecaudado' => $totalRecaudado,
            'cantidadReservas' => $cantidadReservas,
            'totalCuentasPorCobrar' => $totalCuentasPorCobrar,
            'totalFacturadoFiscal' => $totalFacturadoFiscal,
        ];
    }
}
