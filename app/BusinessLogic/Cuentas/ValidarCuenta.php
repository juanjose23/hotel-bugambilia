<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use DomainException;

/**
 * Reglas de negocio para validar operaciones sobre una Cuenta.
 * Unifica BusinessLogic\CuentasEstancia\ValidarCuentaEstancia.
 */
final class ValidarCuenta
{
    /** Valida que la cuenta acepte nuevos cargos */
    public function puedeRegistrarCargo(Cuenta $cuenta): void
    {
        if (! $cuenta->estado->permiteNuevosCargos()) {
            throw new DomainException(
                "La cuenta {$cuenta->numero_cuenta} está en estado '{$cuenta->estado->getLabel()}' y no acepta nuevos cargos.",
            );
        }
    }

    /** Valida que el nuevo cargo no exceda el límite de crédito autorizado */
    public function validarLimiteAutorizado(Cuenta $cuenta, float $montoCargo): void
    {
        if ($cuenta->limite_autorizado === null) {
            return;
        }

        $nuevoSaldo = (float) $cuenta->saldo + $montoCargo;

        if ($nuevoSaldo > (float) $cuenta->limite_autorizado) {
            throw new DomainException(
                'El cargo de C$ '.number_format($montoCargo, 2, '.', ',').
                ' excede el límite autorizado de C$ '.number_format((float) $cuenta->limite_autorizado, 2, '.', ',').
                '. Saldo actual: C$ '.number_format((float) $cuenta->saldo, 2, '.', ',').'.',
            );
        }
    }

    /** Valida que la cuenta pueda cerrarse definitivamente */
    public function puedeCerrarse(Cuenta $cuenta): void
    {
        if (! $cuenta->estado->puedeCerrarse()) {
            throw new DomainException(
                "Solo se pueden cerrar cuentas en estado Abierta o Pendiente de Pago. Estado actual: '{$cuenta->estado->getLabel()}'.",
            );
        }

        if ($cuenta->tieneSaldoPendiente()) {
            throw new DomainException(
                'No se puede cerrar la cuenta con saldo pendiente de C$ '.number_format((float) $cuenta->saldo, 2, '.', ',').'.',
            );
        }
    }

    /** Valida que la cuenta no esté ya abierta o más avanzada */
    public function puedeAbrirse(Cuenta $cuenta): void
    {
        if (! in_array($cuenta->estado, [EstadoCuenta::SOLICITADA, EstadoCuenta::BLOQUEADA], strict: true)) {
            throw new DomainException(
                "La cuenta {$cuenta->numero_cuenta} no puede abrirse desde el estado '{$cuenta->estado->getLabel()}'.",
            );
        }
    }
}
