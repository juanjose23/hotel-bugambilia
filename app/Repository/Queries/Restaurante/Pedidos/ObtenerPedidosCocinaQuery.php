<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosCocinaQuery
{
    /** @return Collection<int, Pedido> */
    public function ejecutar(): Collection
    {
        return Pedido::query()
            ->with(['mesa'])
            ->with(['items' => function ($query) {
                $query->whereNotIn('estado', [
                    EstadoItemPedido::SERVIDO,
                    EstadoItemPedido::ANULADO,
                ]);
            }, 'items.plato'])
            ->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION])
            ->oldest('created_at')
            ->get();
    }
}
