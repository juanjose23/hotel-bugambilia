<?php

namespace Database\Factories;

use App\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductoVariante>
 */
class ProductoVarianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => null, // Se establece en la relación
            'codigo' => $this->faker->unique()->bothify('??-###'),
            'nombre_variante' => $this->faker->word(),
            'atributos' => $this->faker->optional(0.7)->words(3, true),
            'unidad_medida_id' => $this->faker->optional(0.5)->numberBetween(1, 10),
            'peso' => $this->faker->optional(0.7)->randomFloat(2, 0.1, 50),
            'volumen' => $this->faker->optional(0.5)->randomFloat(4, 0.01, 1000),
            'estado' => 1,
        ];
    }
}
