<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Support\Collection;

final class ObtenerPedidosCocinaQuery
{
    /** @return Collection<int, Pedido> */
    public function ejecutar(?string $area = null): Collection
    {
        $pedidos = Pedido::query()
            ->with(['mesa', 'cliente', 'mesero.persona'])
            ->with(['items' => function ($query) {
                $query->whereNotIn('estado', [
                    EstadoItemPedido::SERVIDO,
                    EstadoItemPedido::ANULADO,
                ]);
            }, 'items.plato'])
            ->whereHas('items', function ($query) {
                $query->whereIn('estado', [
                    EstadoItemPedido::PENDIENTE,
                    EstadoItemPedido::EN_PREPARACION,
                    EstadoItemPedido::LISTO,
                ]);
            })
            ->oldest('created_at')
            ->get();

        if ($area !== null && $area !== '') {
            $pedidos = $pedidos->map(function (Pedido $pedido) use ($area): Pedido {
                $cloned = clone $pedido;
                $cloned->setRelation('items', $pedido->items->filter(function ($item) use ($area): bool {
                    $itemArea = $item->area_cocina?->value;
                    $platoArea = $item->plato?->area_cocina?->value;

                    return $itemArea === $area || $platoArea === $area;
                }));

                return $cloned;
            })->filter(fn (Pedido $pedido): bool => $pedido->items->isNotEmpty())->values();
        }

        return $pedidos;
    }
}
