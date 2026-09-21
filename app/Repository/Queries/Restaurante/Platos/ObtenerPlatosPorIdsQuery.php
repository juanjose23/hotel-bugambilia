<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Platos;

use App\Repository\Models\Restaurante\Plato;
use Illuminate\Support\Collection;

final class ObtenerPlatosPorIdsQuery
{
    /**
     * @param  array<int, int>  $platoIds
     * @return Collection<int, Plato>
     */
    public function ejecutar(array $platoIds): Collection
    {
        return Plato::query()
            ->with(['precios.moneda', 'categoria'])
            ->whereIn('id', $platoIds)
            ->get()
            ->keyBy('id');
    }
}
