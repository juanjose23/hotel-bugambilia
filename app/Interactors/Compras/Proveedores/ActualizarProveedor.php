<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Proveedores;

use App\Repository\Models\Compras\Proveedor;
use App\Repository\Persistencia\Compras\ProveedorRepositorioInterface;

final readonly class ActualizarProveedor
{
    public function __construct(
        private ProveedorRepositorioInterface $proveedorRepositorio,
    ) {}

    /** @param array<string, mixed> $datos */
    public function execute(Proveedor $proveedor, array $datos): Proveedor
    {
        return $this->ejecutar($proveedor, $datos);
    }

    /** @param array<string, mixed> $datos */
    public function ejecutar(Proveedor $proveedor, array $datos): Proveedor
    {
        return $this->proveedorRepositorio->actualizar($proveedor, $datos);
    }
}
