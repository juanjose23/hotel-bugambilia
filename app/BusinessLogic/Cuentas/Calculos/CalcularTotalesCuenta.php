<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas\Calculos;

final class TotalesCuentaResultado
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $descuentoTotal,
        public readonly float $impuestoTotal,
        public readonly float $servicioTotal,
        public readonly float $propinaTotal,
        public readonly float $recargoTotal,
        public readonly float $total,
        public readonly float $totalPagado,
        public readonly float $saldo,
    ) {}
}

final class CalcularTotalesCuenta
{
    public function calcular(
        float $subtotal,
        float $descuentoTotal,
        float $impuestoTotal,
        float $servicioTotal,
        float $propinaTotal,
        float $recargoTotal,
        float $totalPagado,
    ): TotalesCuentaResultado {
        $basePositiva = max(0.0, $subtotal + $impuestoTotal + $servicioTotal + $propinaTotal + $recargoTotal);
        $descuentoAplicable = min(max(0.0, $descuentoTotal), $basePositiva);

        $total = max(0.0, round(
            $subtotal
            - $descuentoAplicable
            + $impuestoTotal
            + $servicioTotal
            + $propinaTotal
            + $recargoTotal,
            2
        ));

        $saldo = max(0.0, round($total - $totalPagado, 2));

        return new TotalesCuentaResultado(
            subtotal: max(0.0, round($subtotal, 2)),
            descuentoTotal: max(0.0, round($descuentoTotal, 2)),
            impuestoTotal: max(0.0, round($impuestoTotal, 2)),
            servicioTotal: max(0.0, round($servicioTotal, 2)),
            propinaTotal: max(0.0, round($propinaTotal, 2)),
            recargoTotal: max(0.0, round($recargoTotal, 2)),
            total: $total,
            totalPagado: max(0.0, round($totalPagado, 2)),
            saldo: $saldo,
        );
    }
}
