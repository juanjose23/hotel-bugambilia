<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Pack;

use App\Repository\Models\Inventario\ProductoKit;
use Illuminate\Database\Eloquent\Collection;

class ObtenerItemsPackQuery
{
    /** @return Collection<int, ProductoKit> */
    public function ejecutar(int $productoPadreId): Collection
    {
        return ProductoKit::query()
            ->with('variante')
            ->where('producto_padre_id', $productoPadreId)
            ->get();
    }
}
