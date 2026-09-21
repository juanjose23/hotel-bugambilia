<?php

declare(strict_types=1);

namespace App\Interactors\Promociones;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Promociones\Promocion;

final readonly class GenerarCodigoPromocion
{
    public function __construct(
        private GenerarCorrelativoCodigoAction $generadorCodigo,
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->ejecutar('PROM', Promocion::class, 'codigo');
    }
}
