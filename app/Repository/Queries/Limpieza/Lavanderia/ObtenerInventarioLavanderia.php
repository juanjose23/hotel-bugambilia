<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Repository\Models\Inventario\Stock;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerInventarioLavanderia
{
    /**
     * @param  int|array<int>  $lavanderiaId
     * @return Builder<Stock>
     */
    public function execute(int|array $lavanderiaId): Builder
    {
        $query = Stock::query()
            ->with(['variante.producto', 'lote'])
            ->where('cantidad', '>', 0);

        if (is_array($lavanderiaId)) {
            return $query->whereIn('ubicacion_id', $lavanderiaId);
        }

        return $query->where('ubicacion_id', $lavanderiaId);
    }
}
