<?php

declare(strict_types=1);

namespace App\Repository\Queries\Monedas;

use App\Repository\Models\Monedas\Moneda;
use Illuminate\Support\Collection;

final class ObtenerMonedasQuery
{
    /** @return Collection<int, Moneda> */
    public function ejecutar(): Collection
    {
        /** @var Collection<int, Moneda> $monedas */
        $monedas = Moneda::query()->get();

        return $monedas;
    }
}
