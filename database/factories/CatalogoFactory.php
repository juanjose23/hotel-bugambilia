<?php

namespace Database\Factories;

use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Catalogo>
 */
class CatalogoFactory extends Factory
{
    /** @var class-string<Catalogo> */
    protected $model = Catalogo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->word(),
            'codigo' => fake()->unique()->bothify('CAT-####'),
            'catalogo_tipo_id' => CatalogoTipoFactory::new(),
            'estado' => 1,
            'orden' => fake()->numberBetween(1, 100),
        ];
    }
}
