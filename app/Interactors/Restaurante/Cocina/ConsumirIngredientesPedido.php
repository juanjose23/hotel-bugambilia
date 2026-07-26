<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\ValidarDisponibilidadIngredientes;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerIngredientesPedidoQuery;

final readonly class ConsumirIngredientesPedido
{
    public function __construct(
        private ObtenerIngredientesPedidoQuery $ingredientesQuery,
        private ValidarDisponibilidadIngredientes $validarDisponibilidad,
        private RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(PedidoItem $item): void
    {
        $consumo = $this->ingredientesQuery->ejecutar($item);

        if ($consumo === null) {
            return;
        }

        foreach ($consumo['ingredientes'] as $ingrediente) {
            $varianteId = $ingrediente->producto_variante_id;
            $stock = $consumo['stocks']->get($varianteId);
            $requerido = (float) $ingrediente->cantidad * (float) $item->cantidad;
            $nombre = $ingrediente->variante->nombre_variante ?? "variante {$varianteId}";
            $disponible = $stock instanceof Stock ? (float) $stock->cantidad_actual : 0.0;

            $this->validarDisponibilidad->validar($requerido, $disponible, $nombre);
        }

        foreach ($consumo['ingredientes'] as $ingrediente) {
            $stock = $consumo['stocks']->get($ingrediente->producto_variante_id);

            if (! $stock instanceof Stock) {
                continue;
            }

            $cantidad = (float) $ingrediente->cantidad * (float) $item->cantidad;
            $stock->cantidad_actual -= $cantidad;
            $this->repositorio->guardarStock($stock);
            $costoUnitario = $stock->lote?->costo_unitario;

            $this->repositorio->registrarMovimiento([
                'tipo' => 'CONSUMO',
                'lote_id' => $stock->lote_id,
                'producto_id' => $ingrediente->variante?->producto_id,
                'cantidad' => -$cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario !== null ? (float) $costoUnitario * $cantidad : null,
                'ubicacion_origen_id' => $consumo['ubicacion_id'],
                'documento_tipo' => 'pedido_item',
                'documento_id' => $item->id,
                'referencia' => "Consumo de ingrediente para pedido #{$item->pedido_id}",
            ]);
        }
    }
}
