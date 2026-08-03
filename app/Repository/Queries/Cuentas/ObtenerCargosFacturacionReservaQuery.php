<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Repository\Models\Cuentas\CargoFacturacion;
use Illuminate\Support\Collection;

final class ObtenerCargosFacturacionReservaQuery
{
    /** @return Collection<int, CargoFacturacion> */
    public function ejecutar(): Collection
    {
        return CargoFacturacion::query()
            ->activos()
            ->orderBy('orden')
            ->get();
    }
}
