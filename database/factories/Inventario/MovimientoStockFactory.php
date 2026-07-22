<?php

namespace Database\Factories\Inventario;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\MovimientoStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoStock>
 */
class MovimientoStockFactory extends Factory
{
    protected $model = MovimientoStock::class;

    public function definition(): array
    {
        $ubicacionAlmacen = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first();
        $producto = Producto::inRandomOrder()->first();

        return [
            'tipo' => 'MOV_ENTRADA',
            'lote_id' => LoteFactory::new(),
            'producto_id' => $producto->id ?? Producto::factory(),
            'cantidad' => fake()->randomFloat(2, 10, 200),
            'ubicacion_origen_id' => null,
            'ubicacion_destino_id' => $ubicacionAlmacen?->id,
            'documento_tipo' => null,
            'documento_id' => null,
            'referencia' => fake()->optional(0.3)->sentence(),
            'creado_por_id' => null,
            'notas' => fake()->optional(0.5)->sentence(),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function salida(): static
    {
        $ubicacionAlmacen = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first();

        return $this->state(fn (array $attrs) => [
            'tipo' => 'MOV_SALIDA',
            'ubicacion_origen_id' => $ubicacionAlmacen?->id,
            'ubicacion_destino_id' => null,
        ]);
    }
}
