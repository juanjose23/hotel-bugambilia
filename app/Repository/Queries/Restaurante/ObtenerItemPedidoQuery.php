<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\PedidoItem;

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
}
