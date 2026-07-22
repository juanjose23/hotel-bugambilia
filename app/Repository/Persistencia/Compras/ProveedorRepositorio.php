<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Repository\Models\Compras\Proveedor;

class ProveedorRepositorio implements ProveedorRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Proveedor
    {
        return Proveedor::create($datos);
    }
}
