<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Support\MonedaHelper;
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
            $cargoFmt = MonedaHelper::formatear($montoCargo, $cuenta->moneda);
            $limiteFmt = MonedaHelper::formatear((float) $cuenta->limite_autorizado, $cuenta->moneda);
            $saldoFmt = MonedaHelper::formatear((float) $cuenta->saldo, $cuenta->moneda);

            throw new DomainException(
                "El cargo de {$cargoFmt} excede el límite autorizado de {$limiteFmt}. Saldo actual: {$saldoFmt}.",
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
                'No se puede cerrar la cuenta con saldo pendiente de '.MonedaHelper::formatear((float) $cuenta->saldo, $cuenta->moneda).'.',
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
