<?php

declare(strict_types=1);

namespace Database\Seeders\Espacios;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Seeder;

class EspacioPrecioHistoricoSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::query()->where('codigo', 'NIO')->first();
        $usd = Moneda::query()->where('codigo', 'USD')->first();

        if (! $nio || ! $usd) {
            return;
        }

        $espacios = Espacio::query()->whereNull('padre_id')->get();

        foreach ($espacios as $espacio) {
            $preciosVigentes = Precio::query()
                ->where('priceable_type', Espacio::class)
                ->where('priceable_id', $espacio->id)
                ->where('moneda_id', $nio->id)
                ->get();

            foreach ($preciosVigentes as $precioObj) {
                $precioNio = (float) $precioObj->precio;
                if ($precioNio <= 0) {
                    continue;
                }

                $precioHistoricoNio = round($precioNio * 1.10, 2);
                $precioHistoricoUsd = round($precioHistoricoNio / 36.5, 2);

                Precio::query()->updateOrCreate(
                    [
                        'priceable_type' => Espacio::class,
                        'priceable_id' => $espacio->id,
                        'moneda_id' => $nio->id,
                        'tipo_precio' => $precioObj->tipo_precio,
                        'fecha_inicio' => now()->subMonths(6)->toDateString(),
                    ],
                    [
                        'fecha_fin' => now()->subMonths(1)->toDateString(),
                        'precio' => $precioHistoricoNio,
                        'estado' => 0, // Histórico / Inactivo
                        'es_oferta' => false,
                    ]
                );

                Precio::query()->updateOrCreate(
                    [
                        'priceable_type' => Espacio::class,
                        'priceable_id' => $espacio->id,
                        'moneda_id' => $usd->id,
                        'tipo_precio' => $precioObj->tipo_precio,
                        'fecha_inicio' => now()->subMonths(6)->toDateString(),
                    ],
                    [
                        'fecha_fin' => now()->subMonths(1)->toDateString(),
                        'precio' => $precioHistoricoUsd,
                        'estado' => 0, // Histórico / Inactivo
                        'es_oferta' => false,
                    ]
                );
            }
        }
    }
}
