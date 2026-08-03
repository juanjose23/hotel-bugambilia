<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

use DomainException;

/**
 * Regla de negocio: un pago parcial debe cubrir al menos el total de los
 * cargos obligatorios aplicados a la cuenta.
 */
final class ValidarPagoCobroCuenta
{
    public function validar(float $monto, float $saldo, float $cargosObligatoriosTotal): void
    {
        if ($saldo > 0 && $monto < $saldo && $cargosObligatoriosTotal > 0 && $monto < $cargosObligatoriosTotal) {
            throw new DomainException(
                'El monto abonado (C$ '.number_format($monto, 2).') es inferior al total de cargos obligatorios aplicados (C$ '.number_format($cargosObligatoriosTotal, 2).').'
            );
        }
    }
}
