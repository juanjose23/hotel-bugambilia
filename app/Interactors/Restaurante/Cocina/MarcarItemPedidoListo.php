<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Pedidos\RecalcularTotalesPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final class MarcarItemPedidoListo
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

            if (! $item instanceof PedidoItem || $item->estado === EstadoItemPedido::LISTO) {
                return $item;
            }

            if ($item->estado !== EstadoItemPedido::EN_PREPARACION) {
                throw new DomainException('El plato debe estar en preparación antes de marcarlo como listo.');
            }

            $item->estado = EstadoItemPedido::LISTO;
            $this->repositorio->guardarItem($item);

            $pedido = $item->pedido()->first();
            if ($pedido instanceof Pedido) {
                if ($this->items->todosListos($pedido->id)) {
                    $pedido->estado = EstadoPedido::LISTO;
                }

                $this->repositorio->guardarPedido($pedido);
                $this->recalcular->ejecutar($pedido);
            }

            return $item;
        });
    }
}
