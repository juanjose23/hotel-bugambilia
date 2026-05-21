<?php

namespace Database\Factories\Compras;

use App\Models\Catalogos\Producto;
use App\Models\Compras\OrdenCompraItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenCompraItem>
 */
class OrdenCompraItemFactory extends Factory
{
    protected $model = OrdenCompraItem::class;

    public function definition(): array
    {
        $cantidad = fake()->randomFloat(2, 1, 100);
        $precioUnitario = fake()->randomFloat(2, 10, 500);

        return [
            'producto_id' => Producto::factory(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => round($cantidad * $precioUnitario, 2),
        ];
    }
}
