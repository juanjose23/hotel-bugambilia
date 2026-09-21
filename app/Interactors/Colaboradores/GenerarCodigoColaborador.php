<?php

declare(strict_types=1);

namespace App\Interactors\Colaboradores;

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Colaboradores\Colaborador;

final readonly class GenerarCodigoColaborador
{
    public function __construct(
        private GenerarCorrelativoCodigoAction $generadorCodigo,
    ) {}

    public function execute(): string
    {
        return $this->generadorCodigo->ejecutar(
            prefix: 'COL',
            modelClass: Colaborador::class,
        );
    }
}
