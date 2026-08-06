<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Cobros;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;

/**
 * Pre-cierra una cuenta activa marcándola como Pendiente de Pago.
 * El cliente deberá pagar para finalizar el ciclo de la cuenta.
 */
final class SolicitarCobroCuenta
{
    public function __construct(
        private readonly CuentaRepositorioInterface $cuentas,
    ) {}

    public function ejecutar(Cuenta $cuenta, ?int $usuarioId): Cuenta
    {
        return $this->cuentas->actualizar($cuenta, [
            'estado' => EstadoCuenta::PENDIENTE_PAGO,
            'actualizado_por' => $usuarioId,
        ]);
    }
}
