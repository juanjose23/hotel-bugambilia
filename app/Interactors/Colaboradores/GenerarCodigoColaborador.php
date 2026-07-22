<?php

declare(strict_types=1);

namespace App\Interactors\Colaboradores;

use App\BusinessLogic\Colaboradores\GeneradorCodigoColaborador;

class GenerarCodigoColaborador
{
    public function __construct(
        private readonly GeneradorCodigoColaborador $generadorCodigo,
    ) {}

    public function execute(): string
    {
        return $this->generadorCodigo->generar();
    }
}
