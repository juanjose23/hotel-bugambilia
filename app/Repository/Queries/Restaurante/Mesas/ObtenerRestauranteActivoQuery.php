<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Repository\Models\Espacios\Espacio;

final class ObtenerRestauranteActivoQuery
{
    public function ejecutar(): bool
    {
        return Espacio::tieneRestauranteActivo();
    }
}
