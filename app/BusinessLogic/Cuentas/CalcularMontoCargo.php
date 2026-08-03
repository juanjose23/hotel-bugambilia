<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Repository\Models\Cuentas\CargoFacturacion;
use App\Repository\Models\Cuentas\Cuenta;

final class CalcularMontoCargo
{
    /** @return array{base: float, monto: float} */
    public function calcular(CargoFacturacion $cargo, Cuenta $cuenta, float $subtotal): array
    {
        $base = match ($cargo->base_calculo) {
            BaseCalculo::SubtotalBruto => $subtotal,
            BaseCalculo::SubtotalNeto => max(0, $subtotal - (float) $cuenta->descuento_total),
            BaseCalculo::TotalConImpuestos => (float) $cuenta->total,
            BaseCalculo::BaseManual => 0.0,
        };
        $valor = (float) $cargo->valor;
        $monto = match ($cargo->modo_calculo) {
            ModoCargo::Porcentaje => round($base * ($valor / 100), 2),
            ModoCargo::MontoFijo => round($valor, 2),
            ModoCargo::Manual => 0.0,
        };

        return ['base' => $base, 'monto' => $monto];
    }
}
