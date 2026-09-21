<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Calculos;

use App\BusinessLogic\Shared\Calculos\CalcularSubtotalItems;

final class CalcularResumenRestaurante
{
    public function __construct(
        private readonly ?CalcularSubtotalItems $subtotalItems = null,
    ) {}

    /**
     * @param  array<int, array{capacidad: int, tarifa: float, por_hora: bool}>  $mesas
     * @param  array<int, array{cantidad: int, precio: float}>  $preorden
     * @return array{horas: int, mesas_requeridas: int, mesas_seleccionadas: int, capacidad_total: int, costo_mesas: float, costo_preorden: float, subtotal: float, abono_50: float}
     */
    public function calcular(int $comensales, int $horas, array $mesas, array $preorden, bool $cobrarTarifaMesa = true): array
    {
        $horas = max(1, $horas);
        $comensales = max(1, $comensales);
        $capacidadReferencia = max(1, (int) ($mesas[0]['capacidad'] ?? 1));
        $mesasRequeridas = (int) ceil($comensales / $capacidadReferencia);
        $capacidadTotal = array_sum(array_column($mesas, 'capacidad'));
        $costoMesas = 0.0;

        if ($cobrarTarifaMesa) {
            foreach ($mesas as $mesa) {
                $costoMesas += $mesa['tarifa'] * ($mesa['por_hora'] ? $horas : 1);
            }
        }

        $calculator = $this->subtotalItems ?? new CalcularSubtotalItems;
        $costoPreorden = $calculator->calcular($preorden);

        $subtotal = round($costoMesas + $costoPreorden, 2);

        return [
            'horas' => $horas,
            'mesas_requeridas' => $mesasRequeridas,
            'mesas_seleccionadas' => count($mesas),
            'capacidad_total' => $capacidadTotal,
            'costo_mesas' => round($costoMesas, 2),
            'costo_preorden' => round($costoPreorden, 2),
            'subtotal' => $subtotal,
            'abono_50' => round($subtotal * 0.50, 2),
        ];
    }
}
