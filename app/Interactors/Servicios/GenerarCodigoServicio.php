<?php

declare(strict_types=1);

namespace App\Interactors\Servicios;

use App\Repository\Models\Servicios\Servicio;
use App\Services\Shared\GeneradorCodigoService;

class GenerarCodigoServicio
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->generarCorrelativo('SRV', Servicio::class, 'codigo');
    }
}
