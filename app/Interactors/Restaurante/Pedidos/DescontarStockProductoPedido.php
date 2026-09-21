<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;

final class DescontarStockProductoPedido
{
    /**
     * Descuenta del stock polimórfico (stockable) las unidades de un producto
     * vendido en el restaurante o bar, soportando productos simples y con variantes.
     */
    public function ejecutar(PedidoItem $item): void
    {
        if ($item->producto_id === null) {
            return;
        }

        $pedido = $item->pedido;
        $espacio = $pedido?->mesa;

        $stockQuery = Stock::query();

        // 1. Filtrar por stockable (Mesa, Restaurante/Bar padre o Ubicación)
        if ($espacio instanceof Espacio) {
            $stockableIds = array_filter([$espacio->id, $espacio->padre_id]);
            $ubicacionId = $espacio->getAttribute('ubicacion_id');
            if (is_numeric($ubicacionId)) {
                $stockableIds[] = (int) $ubicacionId;
            }

            $stockQuery->where(function ($q) use ($stockableIds): void {
                $q->where(function ($sub) use ($stockableIds): void {
                    $sub->where('stockable_type', Espacio::class)
                        ->whereIn('stockable_id', $stockableIds);
                })->orWhere(function ($sub) use ($stockableIds): void {
                    $sub->where('stockable_type', Ubicacion::class)
                        ->whereIn('stockable_id', $stockableIds);
                });
            });
        }

        // 2. Filtrar por producto y variante
        if ($item->producto_variante_id !== null && $item->producto_variante_id > 0) {
            $stockQuery->where('producto_variante_id', $item->producto_variante_id);
        } else {
            $stockQuery->where('producto_id', $item->producto_id)
                ->whereNull('producto_variante_id');
        }

        /** @var Stock|null $stock */
        $stock = $stockQuery->first();

        // Fallback para producto simple si no hay fila con variante NULL:
        if ($stock === null && $item->producto_variante_id === null) {
            $stock = Stock::query()
                ->where('producto_id', $item->producto_id)
                ->first();
        }

        if ($stock instanceof Stock) {
            $cantRequerida = (float) $item->cantidad;
            $stock->cantidad_actual = max(0.0, (float) $stock->cantidad_actual - $cantRequerida);
            $stock->ultima_verificacion = now();
            $stock->save();
        }
    }
}
