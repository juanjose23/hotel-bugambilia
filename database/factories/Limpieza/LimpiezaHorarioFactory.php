<?php

declare(strict_types=1);

namespace Database\Factories\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaHorario;
use App\Repository\Models\Limpieza\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LimpiezaHorario>
 */
class LimpiezaHorarioFactory extends Factory
{
    protected $model = LimpiezaHorario::class;

    public function definition(): array
    {
        return [
            'turno_id' => Turno::factory(),
            'hora_estimada' => fake()->randomElement(['08:00:00', '09:00:00', '10:00:00', '11:00:00']),
            'duracion_estimada_minutos' => 30,
            'frecuencia' => fake()->randomElement(['diaria', 'semanal']),
            'dia_semana' => fake()->optional()->randomElement(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']),
            'checklist' => [
                'Tender camas y cambiar sábanas' => true,
                'Sacudir polvo de superficies y mobiliario' => true,
                'Limpiar y desinfectar el cuarto de baño' => true,
                'Barrer y trapear los pisos' => true,
                'Reponer toallas limpias' => true,
                'Reponer amenidades (jabón, shampoo, café)' => true,
                'Vaciar papeleras y colocar bolsas nuevas' => true,
            ],
            'activo' => true,
        ];
    }

    public function semanal(): static
    {
        return $this->state(fn (array $attrs) => [
            'frecuencia' => 'semanal',
            'dia_semana' => fake()->randomElement(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']),
        ]);
    }
}
