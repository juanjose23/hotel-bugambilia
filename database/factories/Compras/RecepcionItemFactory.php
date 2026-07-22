<?php

namespace Database\Factories\Compras;

use App\Repository\Models\Compras\RecepcionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecepcionItem>
 */
class RecepcionItemFactory extends Factory
{
    protected $model = RecepcionItem::class;

    public function definition(): array
    {
        return [
            'orden_item_id' => OrdenCompraItemFactory::new(),
            'cantidad_recibida' => fake()->randomFloat(2, 1, 100),
            'cantidad_rechazada' => fake()->randomFloat(2, 0, 10),
        ];
    }
}
