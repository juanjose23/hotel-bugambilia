<?php

declare(strict_types=1);

namespace Database\Factories\Limpieza;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\LimpiezaHorarioDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LimpiezaHorarioDetalle>
 */
class LimpiezaHorarioDetalleFactory extends Factory
{
    protected $model = LimpiezaHorarioDetalle::class;

    public function definition(): array
    {
        return [
            'horario_id' => LimpiezaHorario::factory(),
            'limpiable_type' => Habitacion::class,
            'limpiable_id' => Habitacion::factory(),
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
