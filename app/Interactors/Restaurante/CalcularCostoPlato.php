<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Shared\Stock;

final class CalcularCostoPlato
{
    /**
     * Calcula el costo total de un plato sumando los costos de sus ingredientes.
     * El costo de cada ingrediente se obtiene del Stock en Cocina Restaurante → Lote → costo_unitario.
     *
     * @return array{costo_ingredientes: float, margen_sugerido_pct: int, precio_sugerido: float, items: array<int, array{nombre: string, cantidad: float, costo_unitario: float, costo_total: float, con_stock: bool}>}
     */
    public function ejecutar(int $productoRecetaId): array
    {
        $ingredientes = ProductoKit::with(['variante.producto', 'productoPadre'])
            ->where('producto_padre_id', $productoRecetaId)
            ->get();

        $cocinaId = Ubicacion::where('nombre', 'Cocina Restaurante')->first()?->id;

        $costoTotal = 0.0;
        $detalle = [];

        foreach ($ingredientes as $ingrediente) {
            $variante = $ingrediente->variante;
            $nombre = $variante !== null
                ? $variante->nombre_variante
                : ($ingrediente->productoPadre->nombre ?? 'Ingrediente');

            $cantidad = (float) $ingrediente->cantidad;
            $costoUnitario = 0.0;
            $conStock = false;

            if ($cocinaId && $variante) {
                $stock = Stock::with('lote')
                    ->where('stockable_type', Ubicacion::class)
                    ->where('stockable_id', $cocinaId)
                    ->where('producto_variante_id', $variante->id)
                    ->where('cantidad_actual', '>', 0)
                    ->first();

                if ($stock && $stock->lote?->costo_unitario) {
                    $costoUnitario = (float) $stock->lote->costo_unitario;
                    $conStock = true;
                }
            }

            $costoIngrediente = round($costoUnitario * $cantidad, 2);
            $costoTotal += $costoIngrediente;

            $detalle[] = [
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoIngrediente,
                'con_stock' => $conStock,
            ];
        }

        $margenSugerido = match (true) {
            $costoTotal < 50 => 70,
            $costoTotal < 100 => 65,
            $costoTotal < 200 => 60,
            default => 55,
        };

        $precioSugerido = round($costoTotal / (1 - $margenSugerido / 100), 2);

        return [
            'costo_ingredientes' => $costoTotal,
            'margen_sugerido_pct' => $margenSugerido,
            'precio_sugerido' => $precioSugerido,
            'items' => $detalle,
        ];
    }
}
