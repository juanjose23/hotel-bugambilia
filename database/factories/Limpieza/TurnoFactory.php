<?php

declare(strict_types=1);

namespace Database\Factories\Limpieza;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turno>
 */
class TurnoFactory extends Factory
{
    protected $model = Turno::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Turno Matutino A', 'Turno Matutino B', 'Turno Vespertino A', 'Turno Vespertino B', 'Turno Nocturno']),
            'lider_id' => Colaborador::factory(),
            'apoyo_id' => Colaborador::factory(),
            'hora_inicio' => fake()->randomElement(['07:00:00', '08:00:00', '15:00:00', '23:00:00']),
            'hora_fin' => fake()->randomElement(['15:00:00', '16:00:00', '23:00:00', '07:00:00']),
            'estado' => true,
        ];
    }
}
