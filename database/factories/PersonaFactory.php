<?php

namespace Database\Factories;

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
            'tipo_persona' => 'natural',
            'pais_id' => PaisFactory::new(),
            'telefono' => fake()->phoneNumber(),
            'direccion' => fake()->address(),
        ];
    }
}
