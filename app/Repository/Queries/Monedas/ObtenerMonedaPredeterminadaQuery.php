<?php

declare(strict_types=1);

namespace App\Repository\Queries\Monedas;

use App\Repository\Models\Monedas\Moneda;

final class ObtenerMonedaPredeterminadaQuery
{
    public function ejecutar(): ?Moneda
    {
        /** @var Moneda|null $moneda */
        $moneda = Moneda::query()
            ->where('es_predeterminada', true)
            ->first();

        if ($moneda instanceof Moneda) {
            return $moneda;
        }

        /** @var Moneda|null $primera */
        $primera = Moneda::query()->first();

        return $primera;
    }
}
