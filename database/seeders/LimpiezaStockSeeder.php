<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Shared\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LimpiezaStockSeeder extends Seeder
{
    /**
     * @var array<int, array{codigo: string, ideal: float, costo_unitario: float}>
     */
    private array $kitHabitacion = [
        ['codigo' => 'JB-015-P', 'ideal' => 2.0, 'costo_unitario' => 3.75],
        ['codigo' => 'SH-030-S', 'ideal' => 1.0, 'costo_unitario' => 8.50],
        ['codigo' => 'AC-030-S', 'ideal' => 1.0, 'costo_unitario' => 9.00],
        ['codigo' => 'CR-030-S', 'ideal' => 1.0, 'costo_unitario' => 15.00],
        ['codigo' => 'SB-KING-BCO', 'ideal' => 1.0, 'costo_unitario' => 45.00],
        ['codigo' => 'SE-KING-BCO', 'ideal' => 1.0, 'costo_unitario' => 42.00],
        ['codigo' => 'FA-50-70-BCO', 'ideal' => 2.0, 'costo_unitario' => 12.00],
        ['codigo' => 'T-BANIO-BCO', 'ideal' => 2.0, 'costo_unitario' => 28.00],
        ['codigo' => 'T-MANOS-BCO', 'ideal' => 2.0, 'costo_unitario' => 18.00],
        ['codigo' => 'T-PISO-BCO', 'ideal' => 1.0, 'costo_unitario' => 35.00],
        ['codigo' => 'PH-ROLLO-STD', 'ideal' => 2.0, 'costo_unitario' => 2.50],
        ['codigo' => 'AG-500-BOT', 'ideal' => 2.0, 'costo_unitario' => 1.80],
    ];

    public function run(): void
    {
        $carritoA = Ubicacion::where('nombre', 'Carrito Limpieza A')->first();
        $carritoB = Ubicacion::where('nombre', 'Carrito Limpieza B')->first();

        $this->seedCarritoStock($carritoA);
        $this->seedCarritoStock($carritoB);
        $this->seedHabitacionSharedStock();
    }

    private function seedCarritoStock(?Ubicacion $carrito): void
    {
        if (! $carrito) {
            return;
        }

        foreach ($this->kitHabitacion as $item) {
            $variant = DB::table('producto_variantes')
                ->where('codigo', $item['codigo'])
                ->first();

            if (! $variant) {
                continue;
            }

            $exists = DB::table('inv_stock')
                ->where('producto_id', $variant->producto_id)
                ->where('producto_variante_id', $variant->id)
                ->where('ubicacion_id', $carrito->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $costoTotal = $item['costo_unitario'] * 50.0;
            $loteId = DB::table('inv_lotes')->insertGetId([
                'codigo_lote' => 'LOTE-CARRITO-'.$carrito->id.'-'.$variant->id,
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'estado' => EstadoLote::Disponible->value,
                'cantidad_disponible' => 50.0,
                'cantidad_inicial' => 50.0,
                'costo_unitario' => $item['costo_unitario'],
                'costo_total' => $costoTotal,
                'ubicacion_id' => $carrito->id,
                'fecha_vencimiento' => now()->addMonths(12),
                'fecha_recepcion' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inv_stock')->insert([
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'lote_id' => $loteId,
                'ubicacion_id' => $carrito->id,
                'cantidad' => 50.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedHabitacionSharedStock(): void
    {
        $habitaciones = Habitacion::all();

        foreach ($habitaciones as $habitacion) {
            foreach ($this->kitHabitacion as $item) {
                $variant = DB::table('producto_variantes')
                    ->where('codigo', $item['codigo'])
                    ->first();

                if (! $variant) {
                    continue;
                }

                Stock::firstOrCreate(
                    [
                        'stockable_type' => Habitacion::class,
                        'stockable_id' => $habitacion->id,
                        'producto_variante_id' => $variant->id,
                    ],
                    [
                        'cantidad_ideal' => $item['ideal'],
                        'cantidad_actual' => $item['ideal'], // fully stocked
                    ]
                );
            }
        }
    }
}
