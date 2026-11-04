<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;

final readonly class BloquearLlegadaReservaMesaQuery
{
    public function mesa(int $id): Espacio
    {
        return Espacio::query()->lockForUpdate()->findOrFail($id);
    }

    public function reserva(int $id): Reserva
    {
        return Reserva::query()->lockForUpdate()->findOrFail($id);
    }
}
