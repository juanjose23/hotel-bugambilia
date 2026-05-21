<?php

namespace Database\Factories\Inventario;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        $ubicacionAlmacen = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first();
        $producto = Producto::inRandomOrder()->first();
        $proveedor = Proveedor::inRandomOrder()->first();

        return [
            'codigo_lote' => fake()->unique()->bothify('LOT-####-??'),
            'producto_id' => $producto->id ?? Producto::factory(),
            'producto_variante_id' => ProductoVariante::inRandomOrder()->first()?->id,
            'proveedor_id' => $proveedor->id ?? Proveedor::factory(),
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => fake()->randomFloat(2, 10, 500),
            'cantidad_inicial' => fn (array $attrs) => $attrs['cantidad_disponible'],
            'ubicacion_id' => $ubicacionAlmacen?->id,
            'fecha_vencimiento' => fake()->boolean(70) ? fake()->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d') : null,
            'lote_proveedor' => fake()->optional(0.5)->bothify('PROV-####'),
            'fecha_recepcion' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'recepcion_item_id' => null,
        ];
    }

    public function enCuarentena(): static
    {
        return $this->state(fn (array $attrs) => [
            'estado' => EstadoLote::Cuarentena,
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn (array $attrs) => [
            'fecha_vencimiento' => now()->subDays(rand(1, 60))->format('Y-m-d'),
        ]);
    }
}
