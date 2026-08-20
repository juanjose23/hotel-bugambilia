<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Repository\Models\Inventario\Stock;

final class ObtenerConsumosDisponiblesCarrito
{
    /**
     * @return array<int, array{nombre: string, max: float, cantidad: int}>
     */
    public function execute(int $carritoId): array
    {
        $stocks = Stock::query()
            ->with(['variante.producto'])
            ->where('ubicacion_id', $carritoId)
            ->where('cantidad', '>', 0)
            ->get();

        $consumos = [];
        foreach ($stocks as $stock) {
            if (! $stock->variante) {
                continue;
            }

            $consumos[(int) $stock->variante->id] = [
                'nombre' => ($stock->variante->producto->nombre ?? '').($stock->variante->nombre_variante ? " ({$stock->variante->nombre_variante})" : ''),
                'max' => (float) $stock->cantidad,
                'cantidad' => 0,
            ];
        }

        return $consumos;
    }
}
