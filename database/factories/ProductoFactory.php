<?php

namespace Database\Factories;

use App\Models\Catalogos\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => CatalogoFactory::new(),
            'marca_id' => CatalogoFactory::new(),
            'nombre' => fake()->word(),
            'descripcion' => fake()->sentence(),
            'unidad_medida_id' => CatalogoFactory::new(),
            'tipo' => fake()->randomElement([1, 2]),
            'estado' => 1,
        ];
    }
}
