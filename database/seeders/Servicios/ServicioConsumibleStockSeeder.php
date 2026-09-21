<?php

declare(strict_types=1);

namespace Database\Seeders\Servicios;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Seeder;

class ServicioConsumibleStockSeeder extends Seeder
{
    public function run(): void
    {
        // Mapeo de insumos de stock por tipo de servicio
        $consumiblesPorServicio = [
            'Masaje Relajante Aromaterapia 60 min' => [
                'aceite' => ['ideal' => 15.0, 'actual' => 12.0],
                'toalla' => ['ideal' => 30.0, 'actual' => 28.0],
                'vela' => ['ideal' => 20.0, 'actual' => 18.0],
            ],
            'Masaje Terapéutico Tejido Profundo 90 min' => [
                'aceite' => ['ideal' => 20.0, 'actual' => 16.0],
                'toalla' => ['ideal' => 35.0, 'actual' => 30.0],
            ],
            'Circuito Hidroterapia & Sauna Seco' => [
                'toalla' => ['ideal' => 50.0, 'actual' => 45.0],
                'sandalias' => ['ideal' => 25.0, 'actual' => 22.0],
            ],
            'Jacuzzi Privado Climatizado' => [
                'toalla' => ['ideal' => 20.0, 'actual' => 18.0],
                'sales' => ['ideal' => 15.0, 'actual' => 14.0],
            ],
            'Lavado y Secado de Ropa por Libra' => [
                'detergente' => ['ideal' => 40.0, 'actual' => 35.0],
                'suavizante' => ['ideal' => 30.0, 'actual' => 28.0],
            ],
            'Planchado Profesional Express' => [
                'gancho' => ['ideal' => 100.0, 'actual' => 85.0],
                'funda' => ['ideal' => 60.0, 'actual' => 50.0],
            ],
            'Limpieza Extra y Cambio de Blancos' => [
                'sabana' => ['ideal' => 40.0, 'actual' => 38.0],
                'toalla' => ['ideal' => 50.0, 'actual' => 46.0],
                'desinfectante' => ['ideal' => 25.0, 'actual' => 20.0],
            ],
            'Coffee Break Ejecutivo (por persona)' => [
                'cafe' => ['ideal' => 50.0, 'actual' => 42.0],
                'azucar' => ['ideal' => 30.0, 'actual' => 26.0],
                'servilleta' => ['ideal' => 200.0, 'actual' => 180.0],
            ],
            'Decoración Romántica Premium' => [
                'vela' => ['ideal' => 30.0, 'actual' => 25.0],
                'copa' => ['ideal' => 12.0, 'actual' => 10.0],
            ],
            'Cesta de Bienvenida Gourmet' => [
                'fruta' => ['ideal' => 15.0, 'actual' => 12.0],
                'chocolate' => ['ideal' => 25.0, 'actual' => 20.0],
            ],
        ];

        // Obtener variantes disponibles en el catálogo
        $variantes = ProductoVariante::with('producto')->get();

        if ($variantes->isEmpty()) {
            return;
        }

        foreach ($consumiblesPorServicio as $nombreServicio => $requerimientos) {
            $servicio = Servicio::where('nombre', $nombreServicio)->first();

            if (! $servicio) {
                continue;
            }

            foreach ($requerimientos as $keyword => $cantidades) {
                // Buscar una variante que coincida por nombre de producto o variante
                $variante = $variantes->first(function (ProductoVariante $v) use ($keyword): bool {
                    $nombreCompleto = strtolower(($v->producto->nombre ?? '').' '.($v->nombre_variante ?? ''));

                    return str_contains($nombreCompleto, $keyword);
                });

                // Si no se encuentra variante específica, tomar una aleatoria disponible
                if (! $variante) {
                    $variante = $variantes->random();
                }

                Stock::updateOrCreate(
                    [
                        'stockable_type' => Servicio::class,
                        'stockable_id' => $servicio->id,
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
