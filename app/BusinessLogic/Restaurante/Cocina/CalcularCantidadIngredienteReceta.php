<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cocina;

use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\PedidoItem;

final class CalcularCantidadIngredienteReceta
{
    public function ejecutar(ProductoKit $ingrediente, PedidoItem $item): float
    {
        $rendimiento = (float) ($item->plato?->receta->rendimiento_porciones ?? 1);
        $rendimiento = $rendimiento > 0 ? $rendimiento : 1.0;

        return round(((float) $ingrediente->cantidad / $rendimiento) * (float) $item->cantidad, 4);
    }
}
