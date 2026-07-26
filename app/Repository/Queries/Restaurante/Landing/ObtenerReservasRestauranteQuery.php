<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Landing;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Collection;

final class ObtenerReservasRestauranteQuery
{
    /** @return Collection<int, Reserva> */
    public function ejecutar(): Collection
    {
        return Reserva::query()
            ->whereNotNull('espacio_id')
            ->latest('id')
            ->limit(20)
            ->get();
    }
}
