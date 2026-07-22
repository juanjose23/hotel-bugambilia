<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Repository\Models\Inventario\Stock;
use Illuminate\Database\Eloquent\Builder;

class ObtenerStockPorUbicacion
{
    /** @return Builder<Stock> */
    public function execute(int $ubicacionId): Builder
    {
        return Stock::query()
            ->where('ubicacion_id', $ubicacionId)
            ->where('cantidad', '>', 0);
    }
}
