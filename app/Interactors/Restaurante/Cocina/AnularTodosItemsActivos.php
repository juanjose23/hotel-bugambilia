<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;

final class AnularTodosItemsActivos
{
    public function __construct(
        private readonly AnularItemPedido $anularItem,
        private readonly ObtenerItemPedidoQuery $items,
    ) {}

    /**
     * Anula todos los items activos (no anulados ni servidos) de un pedido.
     *
     * @return list<PedidoItem>
     */
    public function ejecutar(int $pedidoId): array
    {
        $itemsActivos = $this->items->activosPorPedido($pedidoId);
        $anulados = [];

        foreach ($itemsActivos as $item) {
            try {
                $anulado = $this->anularItem->ejecutar($item->id);
                if ($anulado instanceof PedidoItem) {
                    $anulados[] = $anulado;
                }
            } catch (DomainException) {
                // Item ya no está en estado válido, skip
            }
        }

        return $anulados;
    }
}
