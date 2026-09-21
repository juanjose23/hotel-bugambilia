<?php

declare(strict_types=1);

namespace App\Interactors\Servicios;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Servicios\Servicio;

final readonly class GenerarCodigoServicio
{
    public function __construct(
        private GenerarCorrelativoCodigoAction $generadorCodigo,
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->ejecutar('SRV', Servicio::class, 'codigo');
    }
}
