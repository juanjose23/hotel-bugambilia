<?php

declare(strict_types=1);

namespace App\Repository\Queries\Clientes;

use App\Repository\Models\Clientes\Cliente;
use Illuminate\Support\Collection;

final class ObtenerClientesConPersonaQuery
{
    /** @return Collection<int, Cliente> */
    public function ejecutar(): Collection
    {
        /** @var Collection<int, Cliente> $clientes */
        $clientes = Cliente::query()
            ->with('persona')
            ->get();

        return $clientes;
    }
}
