<?php

declare(strict_types=1);

namespace Database\Factories\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
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
        $numero = fake()->unique()->numberBetween(100, 999);

        return [
            'codigo' => 'HAB-'.$numero,
            'nombre' => fake()->word(),
            'numero' => $numero,
            'slug' => fake()->unique()->slug(),
            'categoria_id' => Catalogo::factory(),
            'ubicacion_id' => Ubicacion::factory(),
            'estado' => EstadoEspacio::Disponible,
        ];
    }
}
