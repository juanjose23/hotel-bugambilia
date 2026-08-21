<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Shared\Stock as SharedStock;

final class ObtenerStockDisponiblePorOrigen
{
    /**
     * @return list<array{stock_id: int, producto_id: int, producto_variante_id: int, lote_id: int, codigo_lote: string, nombre_producto: string, nombre_variante: string, cantidad_disponible: float, categoria_id: int|null}>
     */
    public function execute(?string $tipoOrigen, ?int $origenId, ?int $categoriaId = null): array
    {
        if ($tipoOrigen === null || $origenId === null || $origenId <= 0) {
            return [];
        }

        $categoriaIds = $this->resolverCategoriasIds($categoriaId);

        if ($tipoOrigen === 'habitacion' || $tipoOrigen === 'espacio') {
            $stockableType = $tipoOrigen === 'habitacion' ? Habitacion::class : Espacio::class;

            $query = SharedStock::query()
                ->with(['variante.producto', 'lote'])
                ->where('stockable_type', $stockableType)
                ->where('stockable_id', $origenId)
                ->where('cantidad_actual', '>', 0)
                ->whereNotNull('lote_id')
                ->whereHas('lote', fn ($q) => $q->where('estado', EstadoLote::Disponible));

            if (! empty($categoriaIds)) {
                $query->whereHas('variante.producto', fn ($pq) => $pq->whereIn('categoria_id', $categoriaIds));
            }

            /** @var list<array{stock_id: int, producto_id: int, producto_variante_id: int, lote_id: int, codigo_lote: string, nombre_producto: string, nombre_variante: string, cantidad_disponible: float, categoria_id: int|null}> $resultado */
            $resultado = $query->get()->map(function (SharedStock $stock): array {
                $producto = $stock->variante?->producto;
                $variante = $stock->variante;
                $lote = $stock->lote;

                $productoId = $producto !== null ? (int) $producto->id : 0;
                $varianteId = $variante !== null ? (int) $variante->id : 0;
                $loteId = $lote !== null ? (int) $lote->id : 0;
                $codigoLote = $lote !== null ? (string) $lote->codigo_lote : 'N/A';
                $nombreProd = $producto !== null ? (string) $producto->nombre : 'Producto';
                $nombreVar = $variante !== null ? (string) $variante->nombre_variante : '';

                return [
                    'stock_id' => (int) $stock->id,
                    'producto_id' => $productoId,
                    'producto_variante_id' => $varianteId,
                    'lote_id' => $loteId,
                    'codigo_lote' => $codigoLote,
                    'nombre_producto' => $nombreProd,
                    'nombre_variante' => $nombreVar,
                    'cantidad_disponible' => (float) $stock->cantidad_actual,
                    'categoria_id' => $producto?->categoria_id !== null ? (int) $producto->categoria_id : null,
                ];
            })->values()->all();

            return $resultado;
        }

        if ($tipoOrigen === 'ubicacion' || $tipoOrigen === 'carrito') {
            $query = InventarioStock::query()
                ->with(['variante.producto', 'lote'])
                ->where('ubicacion_id', $origenId)
                ->where('cantidad', '>', 0)
                ->whereNotNull('lote_id')
                ->whereHas('lote', fn ($q) => $q->where('estado', EstadoLote::Disponible));

            if (! empty($categoriaIds)) {
                $query->whereHas('variante.producto', fn ($pq) => $pq->whereIn('categoria_id', $categoriaIds));
            }

            /** @var list<array{stock_id: int, producto_id: int, producto_variante_id: int, lote_id: int, codigo_lote: string, nombre_producto: string, nombre_variante: string, cantidad_disponible: float, categoria_id: int|null}> $resultado */
            $resultado = $query->get()->map(function (InventarioStock $stock): array {
                $producto = $stock->variante?->producto;
                $variante = $stock->variante;
                $lote = $stock->lote;

                $productoId = $producto !== null ? (int) $producto->id : 0;
                $varianteId = $variante !== null ? (int) $variante->id : 0;
                $loteId = $lote !== null ? (int) $lote->id : 0;
                $codigoLote = $lote !== null ? (string) $lote->codigo_lote : 'N/A';
                $nombreProd = $producto !== null ? (string) $producto->nombre : 'Producto';
                $nombreVar = $variante !== null ? (string) $variante->nombre_variante : '';

                return [
                    'stock_id' => (int) $stock->id,
                    'producto_id' => $productoId,
                    'producto_variante_id' => $varianteId,
                    'lote_id' => $loteId,
                    'codigo_lote' => $codigoLote,
                    'nombre_producto' => $nombreProd,
                    'nombre_variante' => $nombreVar,
                    'cantidad_disponible' => (float) $stock->cantidad,
                    'categoria_id' => $producto?->categoria_id !== null ? (int) $producto->categoria_id : null,
                ];
            })->values()->all();

            return $resultado;
        }

        return [];
    }

    /**
     * @return list<int>
     */
    private function resolverCategoriasIds(?int $categoriaId): array
    {
        if ($categoriaId === null || $categoriaId <= 0) {
            return [];
        }

        $hijosIds = Catalogo::query()
            ->where('padre_id', $categoriaId)
            ->pluck('id')
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->all();

        return array_values(array_merge([$categoriaId], $hijosIds));
    }
}
