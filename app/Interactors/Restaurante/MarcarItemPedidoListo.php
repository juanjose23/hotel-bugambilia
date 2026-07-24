<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\ObtenerItemPedidoQuery;
use Illuminate\Support\Facades\DB;

final class MarcarItemPedidoListo
{
    public function __construct(
        private readonly ObtenerItemPedidoQuery $items,
        private readonly ConsumirIngredientesPedido $consumirIngredientes,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(int $itemId): ?PedidoItem
    {
        return DB::transaction(function () use ($itemId): ?PedidoItem {
            $item = $this->items->bloquear($itemId);

            if (! $item instanceof PedidoItem || $item->estado === EstadoItemPedido::LISTO) {
                return $item;
            }

            $this->consumirIngredientes->ejecutar($item);
            $item->estado = EstadoItemPedido::LISTO;
            $this->repositorio->guardarItem($item);

            $pedido = $item->pedido;
            if ($pedido instanceof Pedido) {
                $pedido->estado = $this->items->tienePendientes($pedido->id, $item->id)
                    ? EstadoPedido::EN_PREPARACION
                    : EstadoPedido::SERVIDO;
                $this->repositorio->guardarPedido($pedido);
            }

            return $item;
        });
    }
}
