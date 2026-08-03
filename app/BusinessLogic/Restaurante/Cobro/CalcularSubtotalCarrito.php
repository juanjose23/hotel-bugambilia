<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cobro;

final class CalcularSubtotalCarrito
{
    /**
     * Calcula el subtotal de un carrito de auto-pedido.
     *
     * @param  array<int, array{precio: float, cantidad: int}>  $carrito
     */
    public function calcular(array $carrito): float
    {
        $subtotal = 0.0;
        foreach ($carrito as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }

        return round($subtotal, 2);
    }
}
