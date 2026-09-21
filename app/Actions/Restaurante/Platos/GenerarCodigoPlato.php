<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Platos;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Restaurante\Plato;

final readonly class GenerarCodigoPlato
{
    public function __construct(
        private GenerarCorrelativoCodigoAction $generadorCodigo,
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->ejecutar('PLT', Plato::class, 'codigo');
    }
}
