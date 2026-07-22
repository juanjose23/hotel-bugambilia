<?php

namespace Database\Factories\Compras;

use App\Enums\Compras\EstadoDevolucion;
use App\Repository\Models\Compras\DevolucionCompra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DevolucionCompra>
 */
class DevolucionCompraFactory extends Factory
{
    protected $model = DevolucionCompra::class;

    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('DEV-####-???'),
            'orden_compra_id' => OrdenCompraFactory::new(),
            'recepcion_compra_id' => RecepcionCompraFactory::new(),
            'fecha_devolucion' => now(),
            'estado' => EstadoDevolucion::Borrador,
            'motivo' => fake()->sentence(),
            'documento_externo' => fake()->optional()->bothify('GUIA-DEV-####'),
            'creado_por_id' => 1,
        ];
    }
}
