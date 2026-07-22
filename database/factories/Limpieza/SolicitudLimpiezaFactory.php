<?php

declare(strict_types=1);

namespace Database\Factories\Limpieza;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudLimpieza>
 */
class SolicitudLimpiezaFactory extends Factory
{
    protected $model = SolicitudLimpieza::class;

    public function definition(): array
    {
        return [
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => Habitacion::factory(),
            'personal_id' => null,
            'creador_id' => User::factory(),
            'prioridad' => fake()->randomElement(['baja', 'normal', 'alta']),
            'estado' => EstadoLimpieza::Pendiente,
            'notas' => fake()->optional()->sentence(),
        ];
    }

    public function paraEspacio(): static
    {
        return $this->state(fn (array $attrs) => [
            'limpiable_type' => Espacio::class,
            'limpiable_id' => Espacio::factory(),
        ]);
    }
}
