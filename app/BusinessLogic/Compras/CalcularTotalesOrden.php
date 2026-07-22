<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use Illuminate\Support\Collection;

final class CalcularTotalesOrden
{
    private const TASA_IMPUESTO = 0.15;

    /**
     * @param  Collection<int, mixed>  $items
     * @return array{subtotal: float, impuestos: float, total: float}
     */
    public function calcular(Collection $items): array
    {
        $rawSubtotal = $items->sum('subtotal') ?? 0;
        $subtotal = is_numeric($rawSubtotal) ? (float) $rawSubtotal : 0.0;
        $impuestos = round($subtotal * self::TASA_IMPUESTO, 2);
        $total = round($subtotal + $impuestos, 2);

        return compact('subtotal', 'impuestos', 'total');
    }
}
