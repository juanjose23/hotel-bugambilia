<?php

namespace Database\Factories;

use App\Models\Colaboradores\Colaborador;
use App\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Colaborador>
 */
class ColaboradorFactory extends Factory
{
    /** @var class-string<Colaborador> */
    protected $model = Colaborador::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persona_id' => Persona::factory(),
            'codigo' => fake()->unique()->bothify('COL-####'),
            'nss' => fake()->numerify('#########'),
            'fecha_ingreso' => fake()->date(),
            'estado' => 1,
        ];
    }
}
