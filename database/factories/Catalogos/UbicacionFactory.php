<?php

declare(strict_types=1);

namespace Database\Factories\Catalogos;

use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ubicacion>
 */
class UbicacionFactory extends Factory
{
    protected $model = Ubicacion::class;

    public function definition(): array
    {
        return [
            'tipo' => fake()->randomElement(['edificio', 'piso', 'sector', 'zona']),
            'nombre' => fake()->unique()->city(),
            'orden' => fake()->unique()->numberBetween(1, 1000),
            'estado' => 1,
        ];
    }
}
