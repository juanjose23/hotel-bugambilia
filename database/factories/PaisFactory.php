<?php

namespace Database\Factories;

use App\Models\Catalogos\Pais;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pais>
 */
class PaisFactory extends Factory
{
    /** @var class-string<Pais> */
    protected $model = Pais::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->country(),
            'codigo' => fake()->unique()->countryCode(),
        ];
    }
}
