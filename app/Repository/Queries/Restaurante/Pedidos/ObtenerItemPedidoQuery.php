<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\PedidoItem;
use Illuminate\Support\Collection;

final class ObtenerItemPedidoQuery
{
    public function bloquear(int $itemId): ?PedidoItem
    {
        return PedidoItem::query()
            ->with(['plato.receta', 'pedido'])
            ->lockForUpdate()
            ->find($itemId);
    }

    public function tienePendientes(int $pedidoId, int $ignorarItemId): bool
    {
        return PedidoItem::query()
            ->where('pedido_id', $pedidoId)
            ->whereKeyNot($ignorarItemId)
            ->where('estado', '!=', EstadoItemPedido::LISTO)
            ->exists();
    }

    public function todosListos(int $pedidoId): bool
    {
        return ! PedidoItem::query()
            ->where('pedido_id', $pedidoId)
            ->whereIn('estado', [EstadoItemPedido::PENDIENTE, EstadoItemPedido::EN_PREPARACION])
            ->exists();
    }

    public function todosServidos(int $pedidoId): bool
    {
        return ! PedidoItem::query()
            ->where('pedido_id', $pedidoId)
            ->whereNotIn('estado', [EstadoItemPedido::SERVIDO, EstadoItemPedido::ANULADO])
            ->exists();
    }

    /** @return Collection<int, PedidoItem> */
    public function listosPorPedido(int $pedidoId): Collection
    {
        return PedidoItem::query()
            ->where('pedido_id', $pedidoId)
            ->where('estado', EstadoItemPedido::LISTO)
            ->get();
    }

    /** @return Collection<int, PedidoItem> */
    public function activosPorPedido(int $pedidoId): Collection
    {
        return PedidoItem::query()
            ->where('pedido_id', $pedidoId)
            ->whereNotIn('estado', [EstadoItemPedido::ANULADO, EstadoItemPedido::SERVIDO])
            ->get();
    }
}
