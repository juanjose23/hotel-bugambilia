<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosPantallaQuery
{
    /**
     * @return array{enPreparacion: Collection<int, Pedido>, listos: Collection<int, Pedido>}
     */
    public function ejecutar(): array
    {
        $enPreparacion = Pedido::query()
            ->with(['mesa', 'items.plato'])
            ->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION])
            ->latest('id')
            ->limit(12)
            ->get();

        $listos = Pedido::query()
            ->with(['mesa', 'items.plato'])
            ->where('estado', EstadoPedido::LISTO)
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return [
            'enPreparacion' => $enPreparacion,
            'listos' => $listos,
        ];
    }
}
