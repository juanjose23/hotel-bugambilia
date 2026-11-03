<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Restaurante\Pedido;

final readonly class ObtenerSeguimientoPedidoQuery
{
    public function pedido(int $id): Pedido
    {
        return Pedido::query()->with('items.plato', 'cuenta.moneda')->findOrFail($id);
    }

    public function transaccion(int $pedidoId): ?PagoTransaccion
    {
        return PagoTransaccion::query()->where('request_payload->pedido_id', $pedidoId)->latest('id')->first();
    }
}
