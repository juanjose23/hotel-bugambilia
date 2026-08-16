<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Clientes\Cliente;
use Illuminate\Database\Eloquent\Collection;

final class HuespedesQuery
{
    /**
     * @return Collection<int, Cliente>
     */
    public function todosConReservas(): Collection
    {
        return Cliente::with(['persona', 'reservas'])->get();
    }
}
