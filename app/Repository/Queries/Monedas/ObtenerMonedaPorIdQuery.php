<?php

declare(strict_types=1);

namespace App\Repository\Queries\Monedas;

use App\Repository\Models\Monedas\Moneda;

final class ObtenerMonedaPorIdQuery
{
    public function ejecutar(int $id): ?Moneda
    {
        /** @var Moneda|null $moneda */
        $moneda = Moneda::query()->find($id);

        return $moneda;
    }
}
