<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Proveedores;

use App\Repository\Models\Compras\Proveedor;
use App\Repository\Persistencia\Compras\ProveedorRepositorioInterface;

final readonly class CrearProveedor
{
    public function __construct(
        private ProveedorRepositorioInterface $proveedorRepositorio,
    ) {}

    /** @param array<string, mixed> $datos */
    public function ejecutar(array $datos): Proveedor
    {
        return $this->proveedorRepositorio->crear($datos);
    }
}
