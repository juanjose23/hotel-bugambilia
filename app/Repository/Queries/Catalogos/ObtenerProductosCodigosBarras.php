<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Producto;
use Illuminate\Support\Collection;

final class ObtenerProductosCodigosBarras
{
    /**
     * @return Collection<int, Producto>
     */
    public function ejecutar(?int $productoId = null): Collection
    {
        $query = Producto::query()->with(['variantes']);

        if ($productoId !== null) {
            $query->where('id', $productoId);
        }

        return $query->get();
    }
}
