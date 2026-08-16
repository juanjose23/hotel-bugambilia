<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cocina;

final class CalcularCostoProcesoCocina
{
    /**
     * @param  array<int, array<string, mixed>>|list<array<mixed, mixed>>  $itemsData
     * @return array{costo_total: float, costo_por_plato: float}
     */
    public function calcularDesdeArray(int $cantidadPlatos, array $itemsData): array
    {
        $costoTotal = 0.0;

        foreach ($itemsData as $item) {
            $costoAsignado = is_numeric($item['costo_asignado'] ?? null)
                ? (float) $item['costo_asignado']
                : 0.0;

            if ($costoAsignado <= 0.0) {
                $cantidad = is_numeric($item['cantidad'] ?? null) ? (float) $item['cantidad'] : 1.0;
                $precioUnitario = is_numeric($item['precio_unitario'] ?? null) ? (float) $item['precio_unitario'] : 0.0;
                $costoAsignado = round($cantidad * $precioUnitario, 2);
            }

            $costoTotal += $costoAsignado;
        }

        $costoTotal = round($costoTotal, 2);
        $costoPorPlato = $cantidadPlatos > 0 ? round($costoTotal / $cantidadPlatos, 2) : 0.0;

        return [
            'costo_total' => $costoTotal,
            'costo_por_plato' => $costoPorPlato,
        ];
    }
}
