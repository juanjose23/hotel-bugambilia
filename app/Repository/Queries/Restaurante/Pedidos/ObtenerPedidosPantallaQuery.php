<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosPantallaQuery
{
    /**
     * @return array{enPreparacion: Collection<int, Pedido>, listos: Collection<int, Pedido>}
     */
    public function ejecutar(): array
    {
        $enPreparacion = Pedido::query()
            ->where('estado', '!=', EstadoPedido::CANCELADO)
            ->with(['mesa', 'cliente.persona', 'mesero.persona', 'items' => function ($query) {
                $query->porDespachar();
            }, 'items.plato', 'items.producto', 'items.variante'])
            ->whereHas('items', function ($query) {
                $query->porPreparar();
            })
            ->latest('id')
            ->limit(12)
            ->get();

        $listos = Pedido::query()
            ->where('estado', '!=', EstadoPedido::CANCELADO)
            ->with(['mesa', 'cliente.persona', 'mesero.persona', 'items' => function ($query) {
                $query->porDespachar();
            }, 'items.plato', 'items.producto', 'items.variante'])
            ->whereHas('items', function ($query) {
                $query->where('estado', EstadoItemPedido::LISTO);
            })
            ->whereDoesntHave('items', function ($query) {
                $query->porPreparar();
            })
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return [
            'enPreparacion' => $enPreparacion,
            'listos' => $listos,
        ];
    }
}
