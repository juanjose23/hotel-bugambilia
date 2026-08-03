<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosConItemsQuery
{
    /**
     * @param  int[]  $pedidoIds
     * @return Collection<int, Pedido>
     */
    public function ejecutar(array $pedidoIds): Collection
    {
        /** @var Collection<int, Pedido> $pedidos */
        $pedidos = Pedido::query()
            ->whereIn('id', $pedidoIds)
            ->with(['items.plato', 'mesa', 'cliente.personaNatural'])
            ->get();

        return $pedidos;
    }
}
