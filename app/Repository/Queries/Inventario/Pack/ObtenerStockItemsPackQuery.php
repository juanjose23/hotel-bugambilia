<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Pack;

use App\BusinessLogic\Inventario\Data\Pack\StockItemPackData;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Inventario\Stock;
use Illuminate\Support\Collection;

class ObtenerStockItemsPackQuery
{
    /**
     * @return Collection<int, StockItemPackData>
     */
    public function ejecutar(int $productoPadreId): Collection
    {
        $items = ProductoKit::query()
            ->with('variante')
            ->where('producto_padre_id', $productoPadreId)
            ->get();

        $resultado = collect();

        foreach ($items as $item) {
            $variante = $item->variante;
            if ($variante === null) {
                continue;
            }

            $stockTotal = (float) Stock::query()
                ->where('producto_variante_id', $item->producto_variante_id)
                ->sum('cantidad');

            $resultado->push(new StockItemPackData(
                varianteId: $item->producto_variante_id,
                nombreVariante: $variante->nombre_variante ?? $variante->codigo ?? 'N/A',
                codigo: $variante->codigo ?? '',
                cantidadNecesaria: (float) $item->cantidad,
                stockTotal: $stockTotal,
            ));
        }

        return $resultado;
    }
}
