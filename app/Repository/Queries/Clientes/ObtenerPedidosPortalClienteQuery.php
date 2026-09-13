<?php

declare(strict_types=1);

namespace App\Repository\Queries\Clientes;

use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ObtenerPedidosPortalClienteQuery
{
    /**
     * @return Collection<int, Pedido>
     */
    public function ejecutar(?int $clienteId): Collection
    {
        if ($clienteId === null) {
            return collect();
        }

        /** @var Builder<Pedido> $query */
        $query = Pedido::with([
            'items.plato.precios.moneda',
            'mesa',
            'cuenta',
        ])->orderBy('id', 'desc');

        return $query->where('cliente_id', $clienteId)->get();
    }
}
