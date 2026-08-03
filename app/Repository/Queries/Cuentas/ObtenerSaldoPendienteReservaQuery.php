<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;

final class ObtenerSaldoPendienteReservaQuery
{
    public function ejecutar(int $reservaId): float
    {
        return (float) Cuenta::query()
            ->where('reserva_id', $reservaId)
            ->where('estado', EstadoCuenta::ABIERTA)
            ->sum('saldo');
    }
}
