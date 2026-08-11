<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Cuentas\Cuenta;

final class ObtenerSaldoReserva
{
    public function obtenerSaldoTotal(int $reservaId): float
    {
        $cuentas = Cuenta::query()->where('reserva_id', $reservaId)->get();

        return (float) $cuentas->sum(fn (Cuenta $c) => (float) $c->saldo);
    }
}
