<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas\Calculos;

final class VueltoResultado
{
    public function __construct(
        public readonly float $montoAplicado,
        public readonly float $vuelto,
    ) {}
}

final class CalcularVuelto
{
    public function calcular(float $montoRecibido, float $saldoActual): VueltoResultado
    {
        $monto = round($montoRecibido, 2);
        $saldo = round($saldoActual, 2);

        $montoAplicado = $monto;
        $vuelto = 0.0;

        if ($monto > $saldo && $saldo > 0) {
            $montoAplicado = $saldo;
            $vuelto = round($monto - $saldo, 2);
        }

        return new VueltoResultado(
            montoAplicado: $montoAplicado,
            vuelto: $vuelto,
        );
    }
}
