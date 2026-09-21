<?php

declare(strict_types=1);

namespace App\BusinessLogic\Facturacion\Calculos;

final class IvaProporcionalLineaResultado
{
    public function __construct(
        public readonly float $iva,
        public readonly float $totalLinea,
        public readonly float $ivaPorcentaje,
    ) {}
}

final class CalcularIvaProporcionalFactura
{
    public function calcular(
        float $subtotalDetalle,
        float $descuentoDetalle,
        float $impuestoDetalle,
        float $subtotalVenta,
        float $impuestoTotalVenta,
    ): IvaProporcionalLineaResultado {
        $subtotalVentaValido = max(0.01, $subtotalVenta);
        $proporcion = $subtotalDetalle / $subtotalVentaValido;

        $iva = $impuestoDetalle > 0.0
            ? $impuestoDetalle
            : round($impuestoTotalVenta * $proporcion, 2);

        $totalLinea = round($subtotalDetalle - $descuentoDetalle + $iva, 2);
        $ivaPorcentaje = $subtotalDetalle > 0.0
            ? round(($iva / $subtotalDetalle) * 100, 4)
            : 0.0;

        return new IvaProporcionalLineaResultado(
            iva: $iva,
            totalLinea: $totalLinea,
            ivaPorcentaje: $ivaPorcentaje,
        );
    }
}
