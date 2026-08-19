<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Reportes;

use App\Enums\Restaurante\EstadoPedido;
use Illuminate\Support\Collection;
use stdClass;

final class CalcularReportesRestaurante
{
    /**
     * @param  Collection<int, stdClass>  $pedidos
     * @param  Collection<int, stdClass>  $pedidoItems
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
    public function calcular(Collection $pedidos, Collection $pedidoItems): array
    {
        $totalPedidos = $pedidos->count();
        $sumTotal = $pedidos->sum('subtotal');
        $totalFacturado = is_numeric($sumTotal) ? (float) $sumTotal : 0.0;

        $pedidosPagados = $pedidos->filter(fn ($p) => ($p->estado instanceof EstadoPedido ? $p->estado->value : $p->estado) === EstadoPedido::PAGADO->value)->count();
        $pedidosPendientes = $pedidos->filter(function ($p) {
            $estadoVal = $p->estado instanceof EstadoPedido ? $p->estado->value : $p->estado;

            return in_array($estadoVal, [EstadoPedido::ABIERTO->value, EstadoPedido::EN_PREPARACION->value], true);
        })->count();

        $resumen = [
            'total_pedidos' => $totalPedidos,
            'total_facturado' => round($totalFacturado, 2),
            'pedidos_pagados' => $pedidosPagados,
            'pedidos_pendientes' => $pedidosPendientes,
        ];

        /** @var array<int, array{plato: string, cantidad: float, total: float}> $topPlatos */
        $topPlatos = $pedidoItems
            ->groupBy('plato_id')
            ->map(function (Collection $grupo): array {
                $first = $grupo->first();
                $nombre = is_object($first) && isset($first->plato_nombre) ? (string) $first->plato_nombre : 'Desconocido';

                $cantSum = $grupo->sum('cantidad');
                $totSum = $grupo->sum('subtotal');

                return [
                    'plato' => $nombre,
                    'cantidad' => is_numeric($cantSum) ? (float) $cantSum : 0.0,
                    'total' => is_numeric($totSum) ? round((float) $totSum, 2) : 0.0,
                ];
            })
            ->sortByDesc('cantidad')
            ->take(10)
            ->values()
            ->all();

        /** @var array<int, array{categoria: string, cantidad: float, total: float}> $porCategoria */
        $porCategoria = $pedidoItems
            ->groupBy(fn (stdClass $item): string => (string) ($item->categoria_nombre ?? 'Sin categoría'))
            ->map(function (Collection $grupo, string|int $cat): array {
                $cantSum = $grupo->sum('cantidad');
                $totSum = $grupo->sum('subtotal');

                return [
                    'categoria' => (string) $cat,
                    'cantidad' => is_numeric($cantSum) ? (float) $cantSum : 0.0,
                    'total' => is_numeric($totSum) ? round((float) $totSum, 2) : 0.0,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        return [
            'resumen' => $resumen,
            'topPlatos' => $topPlatos,
            'porCategoria' => $porCategoria,
            'totalPedidos' => $totalPedidos,
        ];
    }
}
