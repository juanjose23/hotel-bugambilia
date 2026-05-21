<?php

namespace Database\Factories;

use App\Models\Catalogos\CatalogoTipo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogoTipo>
 */
class CatalogoTipoFactory extends Factory
{
    /** @var class-string<CatalogoTipo> */
    protected $model = CatalogoTipo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->word(),
            'codigo' => fake()->unique()->bothify('TYPE-####'),
            'estado' => 1,
        ];
    }
}
