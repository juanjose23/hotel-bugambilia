<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Stock;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;

final class ObtenerStockDisponibleRestauranteQuery
{
    /**
     * Consulta el stock disponible mediante la relación polimórfica stockable
     * contemplando productos simples (sin variante) y productos con variantes.
     *
     * @return array{disponible: float, tiene_stock: bool, punto_venta: string}
     */
    public function ejecutar(int $productoId, ?int $varianteId = null, ?int $mesaId = null): array
    {
        /** @var Espacio|null $espacio */
        $espacio = $mesaId ? Espacio::query()->with('padre')->find($mesaId) : null;

        $query = Stock::query();

        // 1. Filtrar por el punto de venta (stockable) si se conoce la mesa / espacio
        if ($espacio instanceof Espacio) {
            $stockableIds = array_filter([$espacio->id, $espacio->padre_id]);
            $ubicacionId = $espacio->getAttribute('ubicacion_id');
            if (is_numeric($ubicacionId)) {
                $stockableIds[] = (int) $ubicacionId;
            }

            $query->where(function ($q) use ($stockableIds): void {
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
        if ($varianteId !== null && $varianteId > 0) {
            $query->where('producto_variante_id', $varianteId);
        } else {
            $query->where(function ($q) use ($productoId): void {
                $q->where('producto_id', $productoId)
                    ->whereNull('producto_variante_id');
            });
        }

        $stockTotal = (float) $query->sum('cantidad_actual');

        // Si es producto simple y no se encontró registro directo sin variante,
        // sumar las existencias generales del producto_id
        if ($stockTotal <= 0 && ($varianteId === null || $varianteId === 0)) {
            $stockFallback = (float) Stock::query()
                ->where('producto_id', $productoId)
                ->sum('cantidad_actual');

            if ($stockFallback > 0) {
                $stockTotal = $stockFallback;
            }
        }

        $puntoVenta = $espacio instanceof Espacio
            ? ($espacio->padre instanceof Espacio ? $espacio->padre->nombre : $espacio->nombre)
            : 'Restaurante / Bar General';

        return [
            'disponible' => $stockTotal,
            'tiene_stock' => $stockTotal > 0,
            'punto_venta' => $puntoVenta,
        ];
    }

    /**
     * Retorna el precio base de venta para un producto o variante.
     */
    public function obtenerPrecioVenta(int $productoId, ?int $varianteId = null): float
    {
        $monedaDefault = Moneda::query()->where('es_predeterminada', true)->value('id');

        if ($varianteId !== null && $varianteId > 0) {
            $queryVar = Precio::query()
                ->where('priceable_type', ProductoVariante::class)
                ->where('priceable_id', $varianteId)
                ->where('estado', EstadoGeneral::Activo);

            if (is_numeric($monedaDefault)) {
                $precioMoneda = (clone $queryVar)->where('moneda_id', (int) $monedaDefault)->value('precio');
                if (is_numeric($precioMoneda)) {
                    return (float) $precioMoneda;
                }
            }

            $precioVariante = $queryVar->value('precio');
            if (is_numeric($precioVariante)) {
                return (float) $precioVariante;
            }
        }

        $queryProd = Precio::query()
            ->where('priceable_type', Producto::class)
            ->where('priceable_id', $productoId)
            ->where('estado', EstadoGeneral::Activo);

        if (is_numeric($monedaDefault)) {
            $precioMoneda = (clone $queryProd)->where('moneda_id', (int) $monedaDefault)->value('precio');
            if (is_numeric($precioMoneda)) {
                return (float) $precioMoneda;
            }
        }

        $precioProducto = $queryProd->value('precio');
        if (is_numeric($precioProducto)) {
            return (float) $precioProducto;
        }

        return 0.00;
    }
}
