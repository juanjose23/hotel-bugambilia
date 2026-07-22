<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Devoluciones;

use App\Repository\Models\Compras\DevolucionCompra;
use App\Services\Shared\GeneradorCodigoService;

final class GenerarCodigoDevolucion
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->generarCorrelativo('DEV', DevolucionCompra::class, 'codigo');
    }
}
