<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Restaurante\Pedido;

final readonly class ItemPreordenIncluidoEnReservaQuery
{
    public function ejecutar(Pedido $pedido, int $itemId): bool
    {
        $pedido->loadMissing('cuenta.reserva');
        $reserva = $pedido->cuenta?->reserva;
        $llegada = $reserva?->ultimaEntradaBitacora('llegada_restaurante');

        return is_array($llegada) && ($llegada['pedido_id'] ?? null) === $pedido->id
            && is_array($llegada['items_incluidos'] ?? null)
            && in_array($itemId, $llegada['items_incluidos'], true);
    }
}
