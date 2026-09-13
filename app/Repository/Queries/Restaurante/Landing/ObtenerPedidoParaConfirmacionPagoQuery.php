<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Landing;

use App\Repository\Models\Restaurante\Pedido;

final class ObtenerPedidoParaConfirmacionPagoQuery
{
    public function ejecutar(int $pedidoId): Pedido
    {
        return Pedido::query()
            ->with(['cliente.persona', 'items.plato'])
            ->findOrFail($pedidoId);
    }
}
