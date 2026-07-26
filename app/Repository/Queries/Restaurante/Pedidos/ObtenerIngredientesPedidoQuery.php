<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Collection;

final class ObtenerIngredientesPedidoQuery
{
    /**
     * @return array{ubicacion_id: int, ingredientes: Collection<int, ProductoKit>, stocks: Collection<int, Stock>}|null
     */
    public function ejecutar(PedidoItem $item): ?array
    {
        $item->loadMissing(['plato.receta']);

        $productoId = $item->plato?->receta?->id;
        $cocinaId = Ubicacion::query()->where('nombre', 'Cocina Restaurante')->value('id');

        if (! is_int($productoId) || ! is_numeric($cocinaId)) {
            return null;
        }

        $ingredientes = ProductoKit::query()
            ->with('variante')
            ->where('producto_padre_id', $productoId)
            ->get();

        $varianteIds = $ingredientes->pluck('producto_variante_id')->filter()->values();

        $stocks = Stock::query()
            ->with('lote')
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', (int) $cocinaId)
            ->whereIn('producto_variante_id', $varianteIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('producto_variante_id');

        return [
            'ubicacion_id' => (int) $cocinaId,
            'ingredientes' => $ingredientes,
            'stocks' => $stocks,
        ];
    }
}
