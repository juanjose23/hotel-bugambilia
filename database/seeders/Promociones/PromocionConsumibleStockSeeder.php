<?php

declare(strict_types=1);

namespace Database\Seeders\Promociones;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Seeder;

class PromocionConsumibleStockSeeder extends Seeder
{
    public function run(): void
    {
        $consumiblesPorPromocion = [
            'Escapada Romántica Todo Incluido' => [
                'vela' => ['ideal' => 20.0, 'actual' => 18.0],
                'copa' => ['ideal' => 12.0, 'actual' => 10.0],
                'aceite' => ['ideal' => 15.0, 'actual' => 14.0],
            ],
            'Fin de Semana Gastronómico & Relax' => [
                'copa' => ['ideal' => 24.0, 'actual' => 20.0],
                'servilleta' => ['ideal' => 100.0, 'actual' => 90.0],
            ],
            'Paquete Ejecutivo & Negocios' => [
                'cafe' => ['ideal' => 30.0, 'actual' => 25.0],
                'azucar' => ['ideal' => 20.0, 'actual' => 18.0],
                'funda' => ['ideal' => 15.0, 'actual' => 12.0],
            ],
            'Escapada Familiar Verano & Piscina' => [
                'toalla' => ['ideal' => 40.0, 'actual' => 35.0],
                'sandalias' => ['ideal' => 20.0, 'actual' => 18.0],
            ],
        ];

        $variantes = ProductoVariante::query()->with('producto')->get();

        if ($variantes->isEmpty()) {
            return;
        }

        foreach ($consumiblesPorPromocion as $nombrePromocion => $requerimientos) {
            $promocion = Promocion::query()->where('nombre', $nombrePromocion)->first();

            if (! $promocion) {
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
                        'stockable_type' => Promocion::class,
                        'stockable_id' => $promocion->id,
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
