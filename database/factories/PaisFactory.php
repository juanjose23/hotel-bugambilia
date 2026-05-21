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
        $codigo2 = fake()->unique()->countryCode();

        return [
            'nombre' => fake()->unique()->country(),
            'codigo_iso2' => $codigo2,
            'codigo_iso3' => strtoupper(fake()->unique()->lexify('???')),
            'estado' => 1,
        ];
    }
}
