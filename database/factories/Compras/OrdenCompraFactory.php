<?php

namespace Database\Factories\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\OrdenCompra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenCompra>
 */
class OrdenCompraFactory extends Factory
{
    protected $model = OrdenCompra::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $impuestos = round($subtotal * 0.15, 2);

        return [
            'proveedor_id' => ProveedorFactory::new(),
            'codigo' => fake()->unique()->bothify('OC-####-NNN'),
            'fecha_orden' => now(),
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $subtotal + $impuestos,
            'estado' => EstadoOrdenCompra::Emitida,
        ];
    }
}
