<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Repository\Models\Restaurante\Plato;
use App\Services\Shared\GeneradorCodigoService;

class GenerarCodigoPlato
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->generarCorrelativo('PLT', Plato::class, 'codigo');
    }
}
