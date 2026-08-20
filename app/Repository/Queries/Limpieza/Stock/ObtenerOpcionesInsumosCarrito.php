<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Repository\Models\Inventario\Stock;

final class ObtenerOpcionesInsumosCarrito
{
    /**
     * @return array<int, string>
     */
    public function execute(mixed $carritoId): array
    {
        $carritoId = is_numeric($carritoId) ? (int) $carritoId : 0;

        if ($carritoId <= 0) {
            return [];
        }

        $opciones = [];
        $stocksAgrupados = Stock::query()
            ->with(['variante.producto'])
            ->where('ubicacion_id', $carritoId)
            ->where('cantidad', '>', 0)
            ->get()
            ->groupBy('producto_variante_id');

        foreach ($stocksAgrupados as $varianteId => $stocks) {
            if (! is_numeric($varianteId)) {
                continue;
            }

            /** @var Stock|null $stock */
            $stock = $stocks->first();
            $variante = $stock?->variante;
            $producto = $variante?->producto;

            $nombreProducto = $producto !== null ? (string) $producto->nombre : 'Insumo';
            $nombre = trim($nombreProducto.' '.($variante?->nombre_variante ? "({$variante->nombre_variante})" : ''));
            $disponible = (float) $stocks->sum(fn (Stock $item): float => (float) $item->cantidad);

            $opciones[(int) $varianteId] = "{$nombre} - Disponible: {$disponible}";
        }

        return $opciones;
    }
}
