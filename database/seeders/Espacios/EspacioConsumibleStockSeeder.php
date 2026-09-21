<?php

declare(strict_types=1);

namespace Database\Seeders\Espacios;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Seeder;

class EspacioConsumibleStockSeeder extends Seeder
{
    public function run(): void
    {
        $consumiblesPorEspacio = [
            'Restaurante Bugambilias' => [
                'servilleta' => ['ideal' => 500.0, 'actual' => 450.0],
                'copa' => ['ideal' => 60.0, 'actual' => 54.0],
                'azucar' => ['ideal' => 40.0, 'actual' => 35.0],
            ],
            'Gran Salón de Eventos Real Bugambilias' => [
                'copa' => ['ideal' => 150.0, 'actual' => 140.0],
                'servilleta' => ['ideal' => 300.0, 'actual' => 280.0],
                'cafe' => ['ideal' => 50.0, 'actual' => 45.0],
            ],
            'Gimnasio Fitness Center' => [
                'toalla' => ['ideal' => 50.0, 'actual' => 45.0],
                'desinfectante' => ['ideal' => 15.0, 'actual' => 12.0],
            ],
            'Cabina de Masajes Relax & Spa' => [
                'aceite' => ['ideal' => 25.0, 'actual' => 22.0],
                'toalla' => ['ideal' => 40.0, 'actual' => 36.0],
                'vela' => ['ideal' => 30.0, 'actual' => 26.0],
            ],
            'Piscina Infinity & Pool Lounge' => [
                'toalla' => ['ideal' => 80.0, 'actual' => 75.0],
                'sandalias' => ['ideal' => 30.0, 'actual' => 28.0],
            ],
            'Sala de Juntas & Coworking Ejecutivo' => [
                'cafe' => ['ideal' => 20.0, 'actual' => 18.0],
                'azucar' => ['ideal' => 15.0, 'actual' => 14.0],
            ],
        ];

        $variantes = ProductoVariante::query()->with('producto')->get();

        if ($variantes->isEmpty()) {
            return;
        }

        foreach ($consumiblesPorEspacio as $nombreEspacio => $requerimientos) {
            $espacio = Espacio::query()->where('nombre', $nombreEspacio)->first();

            if (! $espacio) {
                continue;
            }

            foreach ($requerimientos as $keyword => $cantidades) {
                $variante = $variantes->first(function (ProductoVariante $v) use ($keyword): bool {
                    $nombreCompleto = mb_strtolower(($v->producto->nombre ?? '').' '.($v->nombre_variante ?? ''), 'UTF-8');

                    return str_contains($nombreCompleto, $keyword);
                });

                if (! $variante) {
                    $variante = $variantes->random();
                }

                Stock::query()->updateOrCreate(
                    [
                        'stockable_type' => Espacio::class,
                        'stockable_id' => $espacio->id,
                        'producto_variante_id' => $variante->id,
                    ],
                    [
                        'cantidad_ideal' => $cantidades['ideal'],
                        'cantidad_actual' => $cantidades['actual'],
                        'estado' => 'NORMAL',
                        'ultima_verificacion' => now(),
                    ]
                );
            }
        }
    }
}
