<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Reservas\Reserva;

final class ObtenerReservaBloqueadaQuery
{
    public function ejecutar(int $reservaId): Reserva
    {
        return Reserva::query()->lockForUpdate()->findOrFail($reservaId);
    }
}
