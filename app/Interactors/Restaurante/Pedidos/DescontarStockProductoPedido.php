<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Interactors\Restaurante\Cocina\ConsumirExistenciasCocina;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerItemPedidoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class DescontarStockProductoPedido
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias, private ConsumirExistenciasCocina $consumir, private ObtenerItemPedidoQuery $items) {}

    public function ejecutar(PedidoItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $item = $this->items->bloquear($item->id) ?? throw new DomainException('Partida no encontrada.');
            if ($item->producto_id === null || $this->existencias->tieneConsumo($item->id)) {
                return;
            }
            $stocks = $this->existencias->obtener($this->existencias->producto((int) $item->producto_id, $item->producto_variante_id, $item->pedido?->mesa_id !== null ? (int) $item->pedido->mesa_id : null), true);
            $this->consumir->ejecutar($stocks, (float) $item->cantidad, ['documento_tipo' => 'pedido_item', 'documento_id' => $item->id, 'referencia' => 'Venta de producto para pedido #'.$item->pedido_id]);
        });
    }
}
