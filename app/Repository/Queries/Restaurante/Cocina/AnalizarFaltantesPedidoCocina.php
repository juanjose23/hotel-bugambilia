<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\CalcularCantidadIngredienteReceta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerIngredientesPedidoQuery;
use App\Repository\Queries\Restaurante\Stock\ObtenerStockDisponibleRestauranteQuery;

final class AnalizarFaltantesPedidoCocina
{
    public function __construct(
        private readonly ObtenerIngredientesPedidoQuery $ingredientesPedido,
        private readonly CalcularCantidadIngredienteReceta $calcularCantidadIngrediente,
        private readonly ObtenerStockDisponibleRestauranteQuery $stockQuery,
    ) {}

    /**
     * @return list<array{
     *     pedido_item_id: int,
     *     plato: string,
     *     producto_original_id: int,
     *     variante_original_id: int,
     *     ingrediente: string,
     *     requerido: float,
     *     disponible: float,
     *     faltante: float
     * }>
     */
    public function ejecutar(Pedido $pedido): array
    {
        $pedido->loadMissing(['items.plato.receta', 'items.producto', 'items.variante']);

        $faltantes = [];

        foreach ($pedido->items as $item) {
            if ($item->estado !== EstadoItemPedido::PENDIENTE) {
                continue;
            }

            if ($item->esProducto()) {
                $productoId = (int) $item->producto_id;
                $varianteId = $item->producto_variante_id !== null ? (int) $item->producto_variante_id : null;
                $mesaId = $pedido->mesa_id !== null ? (int) $pedido->mesa_id : null;

                $stockInfo = $this->stockQuery->ejecutar($productoId, $varianteId, $mesaId);
                $disponible = $stockInfo['disponible'];
                $requerido = (float) $item->cantidad;

                if ($disponible < $requerido) {
                    $nombreItem = $item->producto !== null ? $item->producto->nombre : 'Producto';
                    if ($item->variante !== null) {
                        $nombreItem .= ' ('.$item->variante->nombre_variante.')';
                    }

                    $faltantes[] = [
                        'pedido_item_id' => (int) $item->id,
                        'plato' => $nombreItem,
                        'producto_original_id' => $productoId,
                        'variante_original_id' => (int) ($item->producto_variante_id ?? 0),
                        'ingrediente' => 'Stock en '.$stockInfo['punto_venta'],
                        'requerido' => $requerido,
                        'disponible' => $disponible,
                        'faltante' => max(0.0, $requerido - $disponible),
                    ];
                }

                continue;
            }

            $consumo = $this->ingredientesPedido->ejecutar($item);

            if ($consumo === null) {
                continue;
            }

            foreach ($consumo['ingredientes'] as $ingrediente) {
                $stock = $consumo['stocks']->get($ingrediente->producto_variante_id);
                $requerido = $this->calcularCantidadIngrediente->ejecutar($ingrediente, $item);
                $disponible = $stock instanceof Stock ? (float) $stock->cantidad_actual : 0.0;

                if ($disponible >= $requerido) {
                    continue;
                }

                $faltantes[] = [
                    'pedido_item_id' => (int) $item->id,
                    'plato' => $item->plato->nombre ?? 'Platillo',
                    'producto_original_id' => (int) ($ingrediente->variante->producto_id ?? 0),
                    'variante_original_id' => (int) $ingrediente->producto_variante_id,
                    'ingrediente' => $this->nombreIngrediente($ingrediente),
                    'requerido' => $requerido,
                    'disponible' => $disponible,
                    'faltante' => max(0.0, $requerido - $disponible),
                ];
            }
        }

        return $faltantes;
    }

    private function nombreIngrediente(ProductoKit $ingrediente): string
    {
        $producto = $ingrediente->variante?->producto?->nombre;
        $variante = $ingrediente->variante?->nombre_variante;

        if (is_string($producto) && trim($producto) !== '' && is_string($variante) && trim($variante) !== '') {
            return trim($producto).' - '.trim($variante);
        }

        return (string) ($producto ?: $variante ?: "Variante {$ingrediente->producto_variante_id}");
    }
}
