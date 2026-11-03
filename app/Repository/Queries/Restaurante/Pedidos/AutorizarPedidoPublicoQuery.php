<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use Illuminate\Http\Request;

final readonly class AutorizarPedidoPublicoQuery
{
    public function ejecutar(Request $request, int $pedidoId): bool
    {
        $pedidos = $request->session()->get('pedidos_publicos', []);
        if (is_array($pedidos) && in_array($pedidoId, $pedidos, true)) {
            return true;
        }
        $user = $request->user();
        $clienteId = $user instanceof User ? $user->persona?->cliente?->id : null;

        return $clienteId !== null && Pedido::query()->whereKey($pedidoId)->where('cliente_id', $clienteId)->exists();
    }
}
