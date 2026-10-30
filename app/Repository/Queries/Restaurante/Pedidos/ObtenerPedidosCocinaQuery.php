<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosCocinaQuery
{
    /** @return Collection<int, Pedido> */
    public function ejecutar(?string $area = null): Collection
    {
        return Pedido::query()
            ->where('estado', '!=', EstadoPedido::CANCELADO)
            ->whereHas('items', fn ($q) => $q->porDespachar()->paraArea($area))
            ->with([
                'mesa', 'cliente.persona', 'mesero.persona',
                'items' => fn ($q) => $q->porDespachar()->paraArea($area),
                'items.plato', 'items.producto', 'items.variante',
            ])
            ->oldest('created_at')->orderBy('id')->get();
    }
}
