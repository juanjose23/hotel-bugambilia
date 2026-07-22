<?php

declare(strict_types=1);

namespace App\Repository\Queries\Shared;

use App\Repository\Models\Monedas\Moneda;

class ObtenerMonedaBase
{
    public function ejecutar(): ?Moneda
    {
        return Moneda::query()
            ->where('es_predeterminada', true)
            ->first();
    }
}
