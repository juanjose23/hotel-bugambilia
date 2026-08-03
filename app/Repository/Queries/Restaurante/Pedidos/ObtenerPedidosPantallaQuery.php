<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
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
            ->with(['mesa', 'cliente', 'mesero.persona', 'items' => function ($query) {
                $query->whereNotIn('estado', [
                    EstadoItemPedido::SERVIDO,
                    EstadoItemPedido::ANULADO,
                ]);
            }, 'items.plato'])
            ->whereHas('items', function ($query) {
                $query->whereIn('estado', [
                    EstadoItemPedido::PENDIENTE,
                    EstadoItemPedido::EN_PREPARACION,
                ]);
            })
            ->latest('id')
            ->limit(12)
            ->get();

        $listos = Pedido::query()
            ->with(['mesa', 'cliente', 'mesero.persona', 'items' => function ($query) {
                $query->whereNotIn('estado', [
                    EstadoItemPedido::SERVIDO,
                    EstadoItemPedido::ANULADO,
                ]);
            }, 'items.plato'])
            ->whereHas('items', function ($query) {
                $query->where('estado', EstadoItemPedido::LISTO);
            })
            ->whereDoesntHave('items', function ($query) {
                $query->whereIn('estado', [
                    EstadoItemPedido::PENDIENTE,
                    EstadoItemPedido::EN_PREPARACION,
                ]);
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
