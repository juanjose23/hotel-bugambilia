<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Repository\Models\Inventario\Stock;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerInventarioCarrito
{
    /**
     * @return Builder<Stock>
     */
    public function execute(int $carritoId): Builder
    {
        return Stock::query()
            ->where('ubicacion_id', $carritoId)
            ->where('cantidad', '>', 0)
            ->with(['variante.producto', 'lote']);
    }
}
