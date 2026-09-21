<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

use App\Repository\Models\Monedas\Moneda;
use App\Support\MonedaHelper;
use DomainException;

/**
 * Regla de negocio: un pago parcial debe cubrir al menos el total de los
 * cargos obligatorios aplicados a la cuenta.
 */
final class ValidarPagoCobroCuenta
{
    public function validar(float $monto, float $saldo, float $cargosObligatoriosTotal, ?Moneda $moneda = null): void
    {
        if ($saldo > 0 && $monto < $saldo && $cargosObligatoriosTotal > 0 && $monto < $cargosObligatoriosTotal) {
            $montoFmt = MonedaHelper::formatear($monto, $moneda);
            $cargosFmt = MonedaHelper::formatear($cargosObligatoriosTotal, $moneda);

            throw new DomainException(
                "El monto abonado ({$montoFmt}) es inferior al total de cargos obligatorios aplicados ({$cargosFmt})."
            );
        }
    }
}
