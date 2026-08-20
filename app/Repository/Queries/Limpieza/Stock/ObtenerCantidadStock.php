<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Repository\Models\Inventario\Stock;

final class ObtenerCantidadStock
{
    public function execute(int $stockId): float
    {
        $cantidad = Stock::query()
            ->whereKey($stockId)
            ->value('cantidad');

        return is_numeric($cantidad) ? (float) $cantidad : 0.0;
    }
}
