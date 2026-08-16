<?php

declare(strict_types=1);

namespace App\Repository\Queries\Cuentas;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;

final readonly class ResolverClienteDeCuentaQuery
{
    public function ejecutar(Cuenta $cuenta): ?Cliente
    {
        if ($cuenta->cliente_id === null) {
            return null;
        }

        /** @var Cliente|null $cliente */
        $cliente = Cliente::query()
            ->with('persona')
            ->find($cuenta->cliente_id);

        return $cliente;
    }
}
