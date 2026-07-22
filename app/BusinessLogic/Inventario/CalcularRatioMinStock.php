<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario;

use App\BusinessLogic\Inventario\Data\Pack\StockItemPackData;
use App\Repository\Queries\Inventario\Pack\ObtenerStockItemsPackQuery;
use Illuminate\Support\Collection;

class CalcularRatioMinStock
{
    public function __construct(
        private readonly ObtenerStockItemsPackQuery $obtenerStock,
    ) {}

    public function ejecutar(int $productoPadreId): string
    {
        $items = $this->obtenerStock->ejecutar($productoPadreId);

        if ($items->isEmpty()) {
            return '—';
        }

        /** @var Collection<int, StockItemPackData> $items */
        $ratios = $items->map(fn (StockItemPackData $item) => $item->cantidadNecesaria > 0
            ? (int) floor($item->stockTotal / $item->cantidadNecesaria)
            : 0
        )->values();

        $min = $ratios->min();

        return "$min pack".($min !== 1 ? 's' : '');
    }
}
