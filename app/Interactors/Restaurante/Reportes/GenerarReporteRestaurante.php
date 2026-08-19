<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Reportes;

use App\BusinessLogic\Restaurante\Reportes\CalcularReportesRestaurante;
use App\Repository\Queries\Restaurante\Reportes\ObtenerReportesRestauranteQuery;

final readonly class GenerarReporteRestaurante
{
    public function __construct(
        private ObtenerReportesRestauranteQuery $query,
        private CalcularReportesRestaurante $calculador,
    ) {}

    /**
     * @return array{
     *     resumen: array{
     *         total_pedidos: int,
     *         total_facturado: float,
     *         pedidos_pagados: int,
     *         pedidos_pendientes: int
     *     },
     *     topPlatos: array<int, array{plato: string, cantidad: float, total: float}>,
     *     porCategoria: array<int, array{categoria: string, cantidad: float, total: float}>,
     *     totalPedidos: int
     * }
     */
    public function ejecutar(string $fechaInicio, string $fechaFin): array
    {
        $pedidos = $this->query->obtenerPedidosPorRango($fechaInicio, $fechaFin);
        $items = $this->query->obtenerItemsPorRango($fechaInicio, $fechaFin);

        return $this->calculador->calcular($pedidos, $items);
    }
}
