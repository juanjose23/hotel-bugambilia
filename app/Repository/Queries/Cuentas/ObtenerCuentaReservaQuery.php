<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Repository\Models\Cuentas\Cuenta;

final class ObtenerCuentaReservaQuery
{
    public function ejecutar(int $reservaId): ?Cuenta
    {
        return Cuenta::query()
            ->where('reserva_id', $reservaId)
            ->latest('id')
            ->first();
    }
}
