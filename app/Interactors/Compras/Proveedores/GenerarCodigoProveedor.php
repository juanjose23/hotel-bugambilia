<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Proveedores;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Compras\Proveedor;

final readonly class GenerarCodigoProveedor
{
    public function __construct(
        private GenerarCorrelativoCodigoAction $generadorCodigo,
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->ejecutar('PRV', Proveedor::class, 'codigo');
    }
}
