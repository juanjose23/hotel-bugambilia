<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Platos;

use App\Actions\Restaurante\Platos\GenerarCodigoPlato as GenerarCodigoPlatoAction;

final class GenerarCodigoPlato
{
    public function __construct(
        private readonly GenerarCodigoPlatoAction $action
    ) {}

    public function ejecutar(): string
    {
        return $this->action->ejecutar();
    }
}
