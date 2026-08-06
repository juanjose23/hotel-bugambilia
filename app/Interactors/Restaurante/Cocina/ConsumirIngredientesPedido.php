<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Restaurante\Cocina\CalcularCantidadIngredienteReceta;
use App\BusinessLogic\Restaurante\Cocina\ValidarDisponibilidadIngredientes;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\SustitucionIngrediente;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerIngredientesPedidoQuery;

final readonly class ConsumirIngredientesPedido
{
    public function __construct(
        private ObtenerIngredientesPedidoQuery $ingredientesQuery,
        private ValidarDisponibilidadIngredientes $validarDisponibilidad,
        private CalcularCantidadIngredienteReceta $calcularCantidadIngrediente,
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
            $sustitucion = $this->sustitucionActiva($item, (int) $varianteId);
            $varianteConsumoId = $sustitucion !== null ? (int) $sustitucion->variante_sustituta_id : (int) $varianteId;
            $stock = $varianteConsumoId === (int) $varianteId
                ? $consumo['stocks']->get($varianteId)
                : $this->repositorio->obtenerStockPorVariante((int) $consumo['ubicacion_id'], $varianteConsumoId);
            $requerido = $this->calcularCantidadIngrediente->ejecutar($ingrediente, $item);
            $requerido = $sustitucion !== null ? (float) $sustitucion->cantidad_usada : $requerido;
            $nombre = $sustitucion !== null
                ? $this->nombreSustitucion($sustitucion)
                : $this->nombreIngrediente($ingrediente);
            $disponible = $stock instanceof Stock ? (float) $stock->cantidad_actual : 0.0;

            $this->validarDisponibilidad->validar($requerido, $disponible, $nombre);
        }

        foreach ($consumo['ingredientes'] as $ingrediente) {
            $sustitucion = $this->sustitucionActiva($item, (int) $ingrediente->producto_variante_id);
            $varianteConsumoId = $sustitucion !== null ? (int) $sustitucion->variante_sustituta_id : (int) $ingrediente->producto_variante_id;
            $stock = $varianteConsumoId === (int) $ingrediente->producto_variante_id
                ? $consumo['stocks']->get($ingrediente->producto_variante_id)
                : $this->repositorio->obtenerStockPorVariante((int) $consumo['ubicacion_id'], $varianteConsumoId);

            if (! $stock instanceof Stock) {
                continue;
            }

            $cantidad = $this->calcularCantidadIngrediente->ejecutar($ingrediente, $item);
            $cantidad = $sustitucion !== null ? (float) $sustitucion->cantidad_usada : $cantidad;
            $stock->cantidad_actual -= $cantidad;
            $this->repositorio->guardarStock($stock);
            $costoUnitario = $stock->lote?->costo_unitario;
            $productoId = $sustitucion !== null ? $sustitucion->producto_sustituto_id : ($ingrediente->variante !== null ? $ingrediente->variante->producto_id : null);
            $referencia = $sustitucion !== null
                ? "Consumo de ingrediente sustituto para pedido #{$item->pedido_id}: {$this->nombreIngrediente($ingrediente)} -> {$this->nombreSustitucion($sustitucion)}"
                : "Consumo de ingrediente para pedido #{$item->pedido_id}";

            $this->repositorio->registrarMovimiento([
                'tipo' => 'CONSUMO',
                'lote_id' => $stock->lote_id,
                'producto_id' => $productoId,
                'cantidad' => -$cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario !== null ? (float) $costoUnitario * $cantidad : null,
                'ubicacion_origen_id' => $consumo['ubicacion_id'],
                'documento_tipo' => 'pedido_item',
                'documento_id' => $item->id,
                'referencia' => $referencia,
            ]);
        }
    }

    private function nombreIngrediente(ProductoKit $ingrediente): string
    {
        $variante = $ingrediente->variante;
        $producto = $variante?->producto;
        $nombreProducto = is_string($producto?->nombre) && trim($producto->nombre) !== ''
            ? trim($producto->nombre)
            : null;
        $nombreVariante = is_string($variante?->nombre_variante) && trim($variante->nombre_variante) !== ''
            ? trim($variante->nombre_variante)
            : null;

        if ($nombreProducto !== null && $nombreVariante !== null) {
            return "{$nombreProducto} - {$nombreVariante}";
        }

        return $nombreProducto ?? $nombreVariante ?? "variante {$ingrediente->producto_variante_id}";
    }

    private function sustitucionActiva(PedidoItem $item, int $varianteOriginalId): ?SustitucionIngrediente
    {
        /** @var SustitucionIngrediente|null $sustitucion */
        $sustitucion = SustitucionIngrediente::query()
            ->with(['varianteSustituta.producto'])
            ->where('pedido_item_id', $item->id)
            ->where('variante_original_id', $varianteOriginalId)
            ->where('estado', 1)
            ->latest('id')
            ->first();

        return $sustitucion;
    }

    private function nombreSustitucion(SustitucionIngrediente $sustitucion): string
    {
        $variante = $sustitucion->varianteSustituta;
        $producto = $variante?->producto;
        $nombreProducto = is_string($producto?->nombre) && trim($producto->nombre) !== ''
            ? trim($producto->nombre)
            : null;
        $nombreVariante = is_string($variante?->nombre_variante) && trim($variante->nombre_variante) !== ''
            ? trim($variante->nombre_variante)
            : null;

        if ($nombreProducto !== null && $nombreVariante !== null) {
            return "{$nombreProducto} - {$nombreVariante}";
        }

        return $nombreProducto ?? $nombreVariante ?? "variante {$sustitucion->variante_sustituta_id}";
    }
}
