<?php

namespace Database\Factories\Compras;

use App\Enums\Compras\EstadoRecepcion;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecepcionCompra>
 */
class RecepcionCompraFactory extends Factory
{
    protected $model = RecepcionCompra::class;

    public function definition(): array
    {
        return [
            'orden_compra_id' => OrdenCompraFactory::new(),
            'codigo' => fake()->unique()->bothify('REC-####-NNN'),
            'fecha_recepcion' => now(),
            'recibido_por_id' => User::factory(),
            'estado' => EstadoRecepcion::Pendiente,
        ];
    }
}
