<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Reservas\Reserva;

final readonly class ObtenerUltimaCuentaActivaReservaQuery
{
    public function ejecutar(Reserva $reserva): ?Cuenta
    {
        /** @var Cuenta|null $cuenta */
        $cuenta = $reserva->cuentas()
            ->whereIn('estado', [
                EstadoCuenta::SOLICITADA->value,
                EstadoCuenta::ABIERTA->value,
                EstadoCuenta::BLOQUEADA->value,
                EstadoCuenta::PENDIENTE_PAGO->value,
            ])
            ->latest('id')
            ->lockForUpdate()
            ->first();

        return $cuenta;
    }
}
