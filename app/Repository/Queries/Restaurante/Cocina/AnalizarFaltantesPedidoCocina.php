<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\CalcularCantidadIngredienteReceta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerIngredientesPedidoQuery;

final class AnalizarFaltantesPedidoCocina
{
    public function __construct(
        private readonly ObtenerIngredientesPedidoQuery $ingredientesPedido,
        private readonly CalcularCantidadIngredienteReceta $calcularCantidadIngrediente,
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
        $pedido->loadMissing(['items.plato.receta']);

        $faltantes = [];

        foreach ($pedido->items as $item) {
            if ($item->estado !== EstadoItemPedido::PENDIENTE) {
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
