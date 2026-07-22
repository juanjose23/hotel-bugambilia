<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Facades\DB;

final class ConsumirIngredientesPedido
{
    /**
     * Al marcar un item del pedido como "Listo", consume los ingredientes del stock de cocina.
     */
    public function ejecutar(PedidoItem $pedidoItem): void
    {
        $plato = $pedidoItem->plato;
        if (! $plato) {
            return;
        }

        $productoPadre = $plato->receta;
        if (! $productoPadre instanceof Producto) {
            return;
        }

        $ingredientes = ProductoKit::with('variante')
            ->where('producto_padre_id', $productoPadre->id)
            ->get();

        $cocinaId = Ubicacion::where('nombre', 'Cocina Restaurante')->first()?->id;
        if (! $cocinaId) {
            return;
        }

        DB::transaction(function () use ($ingredientes, $pedidoItem, $cocinaId, $productoPadre) {
            foreach ($ingredientes as $ing) {
                $cantidadConsumir = (float) $ing->cantidad * (float) $pedidoItem->cantidad;

                Stock::where('stockable_type', Ubicacion::class)
                    ->where('stockable_id', $cocinaId)
                    ->where('producto_variante_id', $ing->producto_variante_id)
                    ->decrement('cantidad_actual', $cantidadConsumir);

                MovimientoStock::create([
                    'producto_id' => $productoPadre->id,
                    'producto_variante_id' => $ing->producto_variante_id,
                    'tipo' => 'CONSUMO',
                    'cantidad' => $cantidadConsumir,
                    'fecha' => now(),
                ]);
            }
        });
    }
}
