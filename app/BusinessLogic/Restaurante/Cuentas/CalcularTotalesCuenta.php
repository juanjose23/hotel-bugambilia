<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cuentas;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;
use RuntimeException;

final class CalcularTotalesCuenta
{
    /**
     * Calcula los totales de una cuenta a partir de un Pedido.
     * Los items del pedido deben estar precargados (loadMissing/load).
     *
     * @return array{subtotal: float, descuento: float, impuesto: float, propina: float, total: float}
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

        $descuentoPorcentaje = (float) $pedido->descuento_porcentaje;
        $descuentoMontoDirecto = (float) $pedido->descuento_monto;

        $descuento = $descuentoPorcentaje > 0
            ? ($subtotal * ($descuentoPorcentaje / 100))
            : $descuentoMontoDirecto;

        $baseImponible = max(0.0, $subtotal - $descuento);

        $impuestoPorcentaje = (float) $pedido->impuesto_porcentaje;
        $impuesto = $impuestoPorcentaje > 0 ? ($baseImponible * ($impuestoPorcentaje / 100)) : (float) $pedido->impuesto_monto;

        $propinaPorcentaje = (float) $pedido->propina_porcentaje;
        $propina = $propinaPorcentaje > 0 ? ($baseImponible * ($propinaPorcentaje / 100)) : (float) $pedido->propina_monto;

        $total = $baseImponible + $impuesto + $propina;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuento, 2),
            'impuesto' => round($impuesto, 2),
            'propina' => round($propina, 2),
            'total' => round($total, 2),
        ];
    }
}
