<?php

declare(strict_types=1);

namespace Database\Seeders\Limpieza\Stock;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Stock ideal por habitación para los insumos que el ama de llaves repone.
 *
 * Estas filas de stock compartido (tabla `stocks`) alimentan los interactores
 * de reposición de la limpieza (ObtenerStockIdealLimpiable, ObtenerAbastecimientoSugerido,
 * ProcesarReposicionConsumos, ProcesarReposicionBlancos): representan lo que una
 * habitación debe tener en amenidades y blancos al finalizar la limpieza.
 */
final class LimpiezaStockHabitacionSeeder extends Seeder
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
                        'cantidad_actual' => $item['ideal'],
                    ]
                );
            }
        }
    }
}
