<?php

declare(strict_types=1);

namespace App\Interactors\Promociones;

use App\Repository\Models\Promociones\Promocion;
use App\Services\Shared\GeneradorCodigoService;

final class GenerarCodigoPromocion
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo,
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->generarCorrelativo('PROM', Promocion::class, 'codigo');
    }
}
