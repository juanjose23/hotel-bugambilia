<?php

declare(strict_types=1);

namespace Database\Factories\Espacios;

use App\Repository\Models\Espacios\Espacio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Espacio>
 */
class EspacioFactory extends Factory
{
    protected $model = Espacio::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->word(),
        ];
    }
}
