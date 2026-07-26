<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;

final class ContarPedidosPorEstadoQuery
{
    /** @return array<string, int> */
    public function ejecutar(): array
    {
        $rows = Pedido::query()
            ->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION, EstadoPedido::LISTO])
            ->select('estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->estado->value] = (int) $row->total;
        }

        return [
            'abiertos' => $map[EstadoPedido::ABIERTO->value] ?? 0,
            'en_preparacion' => $map[EstadoPedido::EN_PREPARACION->value] ?? 0,
            'listos' => $map[EstadoPedido::LISTO->value] ?? 0,
        ];
    }
}
