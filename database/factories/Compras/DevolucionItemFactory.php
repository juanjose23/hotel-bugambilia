<?php

namespace Database\Factories\Compras;

use App\Models\Compras\DevolucionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DevolucionItem>
 */
class DevolucionItemFactory extends Factory
{
    protected $model = DevolucionItem::class;

    public function definition(): array
    {
        return [
            'devolucion_id' => DevolucionCompraFactory::new(),
            'cantidad_devolver' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
