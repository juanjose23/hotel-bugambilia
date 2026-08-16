<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Reservas\Reserva;

final class ResumenEjecutivoQuery
{
    /**
     * @return array{
     *   totalReservas: float,
     *   totalCobrado: float,
     *   totalFacturado: float,
     *   reservasCount: int,
     * }
     */
    public function paraRango(string $fechaInicio, string $fechaFin): array
    {
        return [
            'totalReservas' => (float) Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total'),
            'totalCobrado' => (float) Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total_pagado'),
            'totalFacturado' => (float) Factura::whereBetween('fecha_emision', [$fechaInicio, $fechaFin])->sum('total'),
            'reservasCount' => Reserva::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
        ];
    }
}
