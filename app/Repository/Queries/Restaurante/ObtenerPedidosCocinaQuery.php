<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosCocinaQuery
{
    /** @return Collection<int, Pedido> */
    public function ejecutar(): Collection
    {
        return Pedido::query()
            ->with(['items.plato', 'mesa'])
            ->whereIn('estado', [EstadoPedido::ABIERTO, EstadoPedido::EN_PREPARACION])
            ->oldest('created_at')
            ->get();
    }
}
