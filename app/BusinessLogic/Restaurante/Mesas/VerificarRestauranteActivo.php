<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Repository\Queries\Restaurante\Mesas\ObtenerRestauranteActivoQuery;

final class VerificarRestauranteActivo
{
    public function __construct(
        private readonly ObtenerRestauranteActivoQuery $restauranteActivo,
    ) {}

    public function estaActivo(): bool
    {
        return $this->restauranteActivo->ejecutar();
    }
}
