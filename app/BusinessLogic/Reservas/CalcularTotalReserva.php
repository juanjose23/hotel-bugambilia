<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

final class CalcularTotalReserva
{
    /**
     * @param  array<int, array{cantidad: int, precio: float}>  $servicios
     */
    public function calcular(float $precioPrincipal, int $unidades, array $servicios): float
    {
        $total = $precioPrincipal * max(1, $unidades);

        foreach ($servicios as $servicio) {
            $total += $servicio['precio'] * $servicio['cantidad'];
        }

        return round($total, 2);
    }
}
