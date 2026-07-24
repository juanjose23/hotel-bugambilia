<?php

declare(strict_types=1);

namespace App\BusinessLogic\CuentasEstancia;

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Repository\Models\Estancias\CuentaEstancia;
use DomainException;

final class ValidarCuentaEstancia
{
    public function puedeRegistrarMovimiento(CuentaEstancia $cuenta): void
    {
        /** @var EstadoCuentaEstancia $estado */
        $estado = $cuenta->estado;
        if ($estado !== EstadoCuentaEstancia::ABIERTA) {
            throw new DomainException('Solo se pueden registrar movimientos en cuentas abiertas.');
        }
    }

    public function validarLimiteAutorizado(CuentaEstancia $cuenta, float $monto): void
    {
        if ($cuenta->limite_autorizado === null) {
            return;
        }

        $nuevoSaldo = (float) $cuenta->saldo + $monto;

        if ($nuevoSaldo > (float) $cuenta->limite_autorizado) {
            throw new DomainException(
                'El cargo excede el límite autorizado de C$ '.number_format((float) $cuenta->limite_autorizado, 2, '.', ',').'.',
            );
        }
    }

    public function puedeCerrar(CuentaEstancia $cuenta): void
    {
        /** @var EstadoCuentaEstancia $estado */
        $estado = $cuenta->estado;
        if ($estado !== EstadoCuentaEstancia::ABIERTA) {
            throw new DomainException('Solo se pueden cerrar cuentas en estado Abierto.');
        }

        if ((float) $cuenta->saldo > 0) {
            throw new DomainException(
                'No se puede cerrar la cuenta con saldo pendiente de C$ '.number_format((float) $cuenta->saldo, 2, '.', ',').'.',
            );
        }
    }
}
