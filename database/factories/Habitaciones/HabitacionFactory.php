<?php

declare(strict_types=1);

namespace Database\Factories\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Habitacion>
 */
class HabitacionFactory extends Factory
{
    protected $model = Habitacion::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->word(),
            'numero' => fake()->unique()->numberBetween(100, 999),
        ];
    }
}
