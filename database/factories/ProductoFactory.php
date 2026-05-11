<?php

namespace Database\Factories;

use App\Models\Catalogos\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => $this->faker->numberBetween(1, 10),
            'marca_id' => $this->faker->optional()->numberBetween(1, 5),
            'nombre' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'unidad_medida_id' => $this->faker->optional()->numberBetween(1, 10),
            'tipo' => $this->faker->randomElement([1, 2]), // 1=Perecedero, 2=No perecedero
            'estado' => 1,
        ];
    }
}
