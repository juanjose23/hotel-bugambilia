<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final readonly class ObtenerPedidosServidosCuentaQuery
{
    /** @return Collection<int, Pedido> */
    public function ejecutar(int $cuentaId): Collection
    {
        return Pedido::query()->where('cuenta_id', $cuentaId)->where('estado', EstadoPedido::SERVIDO)->with('mesa')->orderBy('id')->get();
    }
}
