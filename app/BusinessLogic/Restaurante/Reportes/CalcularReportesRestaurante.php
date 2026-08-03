<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Reportes;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use Illuminate\Support\Collection;

final class CalcularReportesRestaurante
{
    /**
     * @param  Collection<int, Pedido>  $pedidos
     * @param  Collection<int, PedidoItem>  $pedidoItems
     * @return array{
     *     resumen: array<string, mixed>,
     *     topPlatos: array<int, mixed>,
     *     porCategoria: array<int, mixed>,
     *     totalPedidos: int
     * }
     */
    public function calcular(Collection $pedidos, Collection $pedidoItems): array
    {
        $totalPedidos = $pedidos->count();
        $sumTotal = $pedidos->sum('subtotal');
        $totalFacturado = is_numeric($sumTotal) ? (float) $sumTotal : 0.0;
        $pedidosPagados = $pedidos->where('estado', EstadoPedido::PAGADO)->count();
        $pedidosPendientes = $pedidos->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION])->count();

        $resumen = [
            'total_pedidos' => $totalPedidos,
            'total_facturado' => round($totalFacturado, 2),
            'pedidos_pagados' => $pedidosPagados,
            'pedidos_pendientes' => $pedidosPendientes,
        ];

        $topPlatos = $pedidoItems
            ->groupBy('plato_id')
            ->map(function ($grupo) {
                $first = $grupo->first();
                $nombre = ($first instanceof PedidoItem && $first->plato) ? $first->plato->nombre : 'Desconocido';

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
            ->toArray();

        $porCategoria = $pedidoItems
            ->groupBy(function (PedidoItem $item) {
                return $item->plato?->categoria->nombre ?? 'Sin categoría';
            })
            ->map(function ($grupo, $cat) {
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
            ->toArray();

        return [
            'resumen' => $resumen,
            'topPlatos' => $topPlatos,
            'porCategoria' => $porCategoria,
            'totalPedidos' => $totalPedidos,
        ];
    }
}
