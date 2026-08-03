<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerComandasMesaConDetalleQuery
{
    /** @return Collection<int, Pedido> */
    public function ejecutar(int $mesaId): Collection
    {
        return Pedido::query()
            ->with(['items.plato', 'mesa', 'cliente', 'mesero.persona', 'cuenta.pagos'])
            ->where('mesa_id', $mesaId)
            ->whereIn('estado', [
                EstadoPedido::ABIERTO,
                EstadoPedido::EN_PREPARACION,
                EstadoPedido::LISTO,
                EstadoPedido::SERVIDO,
            ])
            ->oldest('id')
            ->get();
    }
}
