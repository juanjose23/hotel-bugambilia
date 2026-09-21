<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Devoluciones;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Compras\DevolucionCompra;

final readonly class GenerarCodigoDevolucion
{
    public function __construct(
        private GenerarCorrelativoCodigoAction $generadorCodigo,
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->ejecutar('DEV', DevolucionCompra::class, 'codigo');
    }
}
