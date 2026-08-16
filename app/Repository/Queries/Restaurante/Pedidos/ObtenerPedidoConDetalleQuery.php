<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Restaurante\Pedido;

final class ObtenerPedidoConDetalleQuery
{
    public function ejecutar(int $pedidoId): ?Pedido
    {
        /** @var Pedido|null $pedido */
        $pedido = Pedido::query()
            ->with([
                'items.plato',
                'cliente.persona.personaNatural',
                'cliente.persona.personaJuridica',
                'mesero.persona',
                'mesa',
                'cuenta.pagos',
            ])
            ->find($pedidoId);

        return $pedido;
    }
}
