<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use Illuminate\Support\Collection;

final class ObtenerCuentasAbiertasQuery
{
    /** @return Collection<int, Cuenta> */
    public function ejecutar(): Collection
    {
        /** @var Collection<int, Cuenta> $cuentas */
        $cuentas = Cuenta::query()
            ->where('estado', EstadoCuenta::ABIERTA)
            ->with(['cliente', 'estancia.habitacion'])
            ->get();

        return $cuentas;
    }
}
