<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Calculos;

final class AplicarPromocionReserva
{
    /** @return array{subtotal: float, descuento: float, total: float} */
    public function calcular(float $subtotal, ?float $porcentaje, ?float $monto, ?float $precioPaquete = null): array
    {
        $base = $precioPaquete !== null && $precioPaquete > 0 ? $precioPaquete : max(0.0, $subtotal);
        $descuentoDirecto = $porcentaje !== null
            ? $base * min(100, max(0, $porcentaje)) / 100
            : min($base, max(0, $monto ?? 0));
        $total = round(max(0.0, $base - $descuentoDirecto), 2);
        $subtotal = round(max($subtotal, $base), 2);
        $descuento = round($subtotal - $total, 2);

        return ['subtotal' => round($subtotal, 2), 'descuento' => $descuento, 'total' => round($subtotal - $descuento, 2)];
    }
}
