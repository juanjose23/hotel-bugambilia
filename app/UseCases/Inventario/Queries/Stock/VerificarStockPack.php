<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Stock;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Inventario\ProductoKit;
use App\Models\Inventario\Stock;

class VerificarStockPack
{
    /**
     * Verifica si hay stock suficiente en una bodega para armar N packs.
     * Usa la misma lógica de filtrado que ConsumirStock (FEFO, estado de lote).
     *
     * @return array{items: array<int, array{producto: string, variante: string, producto_variante_id: int, tipo_producto: int, necesario: float, disponible: float, suficiente: bool}>, suficiente: bool}
     */
    public function ejecutar(int $productoPackId, int $bodegaOrigenId, float $cantidadPacks = 1.0): array
    {
        $items = ProductoKit::with('variante.producto')
            ->where('producto_padre_id', $productoPackId)
            ->get();

        $resultado = [];
        $todoSuficiente = true;

        foreach ($items as $item) {
            $variante = $item->variante;
            if (! $variante) {
                continue;
            }

            $producto = $variante->producto;
            $necesario = (float) $item->cantidad * $cantidadPacks;

            $disponible = (float) Stock::where('producto_id', $producto->id)
                ->where('ubicacion_id', $bodegaOrigenId)
                ->where('producto_variante_id', $variante->id)
                ->where('cantidad', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('lote_id')
                        ->orWhereHas('lote', function ($sub) {
                            $sub->where('estado', EstadoLote::Disponible)
                                ->where(function ($dateQuery) {
                                    $dateQuery->whereNull('fecha_vencimiento')
                                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                                });
                        });
                })
                ->sum('cantidad');

            $suficiente = $disponible >= $necesario;

            if (! $suficiente) {
                $todoSuficiente = false;
            }

            $resultado[] = [
                'producto' => $producto->nombre,
                'variante' => $variante->nombre_variante,
                'producto_variante_id' => $variante->id,
                'tipo_producto' => $producto->tipo,
                'necesario' => $necesario,
                'disponible' => $disponible,
                'suficiente' => $suficiente,
            ];
        }

        return [
            'items' => $resultado,
            'suficiente' => $todoSuficiente,
        ];
    }
}
