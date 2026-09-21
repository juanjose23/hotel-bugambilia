<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Calculos;

final class CalcularSubtotalItems
{
    /**
     * Calcula el subtotal de una lista de ítems con precio y cantidad.
     *
     * @param  array<int, array{precio?: float|int|numeric-string, precio_unitario?: float|int|numeric-string, cantidad?: int|float|numeric-string}>|list<array<string, mixed>>  $items
     */
    public function calcular(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $precio = is_numeric($item['precio'] ?? null)
                ? (float) $item['precio']
                : (is_numeric($item['precio_unitario'] ?? null) ? (float) $item['precio_unitario'] : 0.0);

            $cantidad = is_numeric($item['cantidad'] ?? null)
                ? (float) $item['cantidad']
                : 1.0;

            $subtotal += $precio * max(0.0, $cantidad);
        }

        return round($subtotal, 2);
    }
}
