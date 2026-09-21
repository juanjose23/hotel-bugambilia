<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cobro;

use App\BusinessLogic\Shared\Calculos\CalcularSubtotalItems;

final class CalcularSubtotalCarrito
{
    public function __construct(
        private readonly ?CalcularSubtotalItems $subtotalItems = null,
    ) {}

    /**
     * Calcula el subtotal de un carrito de auto-pedido.
     *
     * @param  array<int, array{precio: float, cantidad: int}>  $carrito
     */
    public function calcular(array $carrito): float
    {
        $calculator = $this->subtotalItems ?? new CalcularSubtotalItems;

        return $calculator->calcular($carrito);
    }
}
