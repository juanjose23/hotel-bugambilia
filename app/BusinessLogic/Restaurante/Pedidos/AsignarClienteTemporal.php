<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Pedidos;

use App\Repository\Models\Espacios\Espacio;

final class AsignarClienteTemporal
{
    public function resolverNombreCliente(?Espacio $mesa, ?string $nombreClienteExplicit): string
    {
        if (! empty($nombreClienteExplicit)) {
            return trim($nombreClienteExplicit);
        }

        if ($mesa instanceof Espacio) {
            return "Cliente {$mesa->nombre}";
        }

        return 'Cliente Mostrador';
    }
}
