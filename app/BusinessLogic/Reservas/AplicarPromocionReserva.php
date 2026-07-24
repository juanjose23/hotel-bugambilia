<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

final class AplicarPromocionReserva
{
    /** @return array{subtotal: float, descuento: float, total: float} */
    public function calcular(float $subtotal, ?float $porcentaje, ?float $monto): array
    {
        $descuento = $porcentaje !== null
            ? $subtotal * min(100, max(0, $porcentaje)) / 100
            : min($subtotal, max(0, $monto ?? 0));
        $descuento = round(min($subtotal, $descuento), 2);

        return ['subtotal' => round($subtotal, 2), 'descuento' => $descuento, 'total' => round($subtotal - $descuento, 2)];
    }
}
