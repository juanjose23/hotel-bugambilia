<?php

namespace Database\Factories\Compras;

use App\Repository\Models\Compras\Proveedor;
use Database\Factories\CatalogoFactory;
use Database\Factories\PersonaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('PROV-####'),
            'persona_id' => PersonaFactory::new(),
            'tipo_proveedor_id' => CatalogoFactory::new(),
            'direccion_fiscal' => fake()->address(),
            'estado' => 1,
        ];
    }
}
