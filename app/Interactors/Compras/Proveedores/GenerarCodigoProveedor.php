<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Proveedores;

use App\Repository\Models\Compras\Proveedor;
use App\Services\Shared\GeneradorCodigoService;

final class GenerarCodigoProveedor
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->generarCorrelativo('PRV', Proveedor::class, 'codigo');
    }
}
