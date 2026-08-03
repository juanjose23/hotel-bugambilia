<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cuentas;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;
use RuntimeException;

final class CalcularTotalesCuenta
{
    /**
     * Calcula el subtotal operativo de un pedido sumando los items no anulados.
     * Los impuestos, descuentos, servicio y propina se calculan a nivel de cuenta,
     * no a nivel de pedido.
     *
     * @return array{subtotal: float}
     */
    public function calcular(Pedido $pedido): array
    {
        if ($pedido->relationLoaded('items') === false) {
            throw new RuntimeException('Los items del pedido deben estar precargados antes de calcular totales.');
        }

        $subtotal = 0.0;
        foreach ($pedido->items as $item) {
            if ($item->estado !== EstadoItemPedido::ANULADO) {
                $subtotal += (float) $item->subtotal;
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
        ];
    }
}
