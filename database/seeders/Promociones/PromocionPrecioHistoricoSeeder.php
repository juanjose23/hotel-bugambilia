<?php

declare(strict_types=1);

namespace Database\Seeders\Promociones;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Shared\Precio;
use Illuminate\Database\Seeder;

class PromocionPrecioHistoricoSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::query()->where('codigo', 'NIO')->first();
        $usd = Moneda::query()->where('codigo', 'USD')->first();

        if (! $nio || ! $usd) {
            return;
        }

        $promociones = Promocion::query()->get();

        foreach ($promociones as $promocion) {
            $valorPrecio = Precio::query()
                ->where('priceable_type', Promocion::class)
                ->where('priceable_id', $promocion->id)
                ->where('moneda_id', $nio->id)
                ->value('precio');

            $precioNio = is_numeric($valorPrecio) ? (float) $valorPrecio : (is_numeric($promocion->precio_paquete) ? (float) $promocion->precio_paquete : 0.0);

            $precioHistoricoNio = $precioNio > 0 ? round($precioNio * 1.15, 2) : 0.0;
            $precioHistoricoUsd = $precioHistoricoNio > 0 ? round($precioHistoricoNio / 36.5, 2) : 0.0;

            // Registrar precio histórico de temporada pasada (hace 6 meses)
            if ($precioHistoricoNio > 0) {
                Precio::query()->updateOrCreate(
                    [
                        'priceable_type' => Promocion::class,
                        'priceable_id' => $promocion->id,
                        'moneda_id' => $nio->id,
                        'fecha_inicio' => now()->subMonths(6)->toDateString(),
                    ],
                    [
                        'fecha_fin' => now()->subMonths(1)->toDateString(),
                        'precio' => $precioHistoricoNio,
                        'estado' => 0, // Inactivo / Histórico
                        'es_oferta' => false,
                    ]
                );

                Precio::query()->updateOrCreate(
                    [
                        'priceable_type' => Promocion::class,
                        'priceable_id' => $promocion->id,
                        'moneda_id' => $usd->id,
                        'fecha_inicio' => now()->subMonths(6)->toDateString(),
                    ],
                    [
                        'fecha_fin' => now()->subMonths(1)->toDateString(),
                        'precio' => $precioHistoricoUsd,
                        'estado' => 0, // Inactivo / Histórico
                        'es_oferta' => false,
                    ]
                );
            }
        }
    }
}
