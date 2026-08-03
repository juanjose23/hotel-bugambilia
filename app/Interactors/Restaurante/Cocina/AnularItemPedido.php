<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Interactors\Restaurante\Pedidos\RecalcularTotalesPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AnularItemPedido
{
    public function __construct(
        private readonly ObtenerItemPedidoQuery $items,
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly RecalcularTotalesPedido $recalcular,
    ) {}

    public function ejecutar(int $itemId): ?PedidoItem
    {
        return DB::transaction(function () use ($itemId): ?PedidoItem {
            $item = $this->items->bloquear($itemId);

            if (! $item instanceof PedidoItem || $item->estado === EstadoItemPedido::ANULADO) {
                return $item;
            }

            if ($item->estado === EstadoItemPedido::SERVIDO) {
                throw new DomainException('No se puede anular un plato que ya fue servido al cliente.');
            }

            $item->estado = EstadoItemPedido::ANULADO;
            $this->repositorio->guardarItem($item);

            $pedido = $item->pedido;
            if ($pedido instanceof Pedido) {
                $this->recalcular->ejecutar($pedido);
            }

            return $item;
        });
    }
}
