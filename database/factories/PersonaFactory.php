<?php

namespace Database\Factories;

use App\Models\Catalogos\Pais;
use App\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    /** @var class-string<Persona> */
    protected $model = Persona::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'primer_nombre' => fake()->firstName(),
            'segundo_nombre' => fake()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'segundo_apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'pais_id' =>Pais::factory(),
        ];
    }
}
