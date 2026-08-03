<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;

final class MarcarTodosItemsServidos
{
    public function __construct(
        private readonly MarcarItemServido $marcarItemServido,
        private readonly ObtenerItemPedidoQuery $items,
    ) {}

    /**
     * Marca como servidos todos los items LISTO de un pedido.
     *
     * @return list<PedidoItem>
     */
    public function ejecutar(int $pedidoId): array
    {
        $itemsListos = $this->items->listosPorPedido($pedidoId);
        $servidos = [];

        foreach ($itemsListos as $item) {
            try {
                $servido = $this->marcarItemServido->ejecutar($item->id);
                if ($servido instanceof PedidoItem) {
                    $servidos[] = $servido;
                }
            } catch (DomainException) {
                // Item ya no está en estado válido, skip
            }
        }

        return $servidos;
    }
}
