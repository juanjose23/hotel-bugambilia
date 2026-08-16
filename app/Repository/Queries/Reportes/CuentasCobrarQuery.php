<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Collection;

final class CuentasCobrarQuery
{
    /**
     * @return Collection<int, Reserva>
     */
    public function reservasConSaldo(string $fechaInicio, string $fechaFin): Collection
    {
        return Reserva::with(['cliente.persona', 'habitacion'])
            ->where('saldo', '>', 0)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->orderBy('saldo', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Cuenta>
     */
    public function cuentasPendientes(): Collection
    {
        return Cuenta::query()
            ->where('saldo', '>', 0)
            ->get();
    }
}
