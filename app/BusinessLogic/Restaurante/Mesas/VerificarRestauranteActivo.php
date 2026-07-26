<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Repository\Models\Espacios\Espacio;

final class VerificarRestauranteActivo
{
    public function estaActivo(): bool
    {
        return Espacio::tieneRestauranteActivo();
    }
}
