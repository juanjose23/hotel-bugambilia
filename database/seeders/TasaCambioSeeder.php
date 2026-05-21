<?php

namespace Database\Seeders;

use App\Models\General\Moneda;
use App\Models\General\TasaCambio;
use Illuminate\Database\Seeder;

class TasaCambioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sembrar monedas básicas
        $monedas = [
            [
                'codigo' => 'NIO',
                'nombre' => 'Córdoba Nicaragüense',
                'simbolo' => 'C$',
                'es_predeterminada' => true,
            ],
            [
                'codigo' => 'USD',
                'nombre' => 'Dólar Estadounidense',
                'simbolo' => '$',
                'es_predeterminada' => false,
            ],
        ];

        $monedasCreadas = [];
        foreach ($monedas as $m) {
            $monedasCreadas[$m['codigo']] = Moneda::updateOrCreate(
                ['codigo' => $m['codigo']],
                [
                    'nombre' => $m['nombre'],
                    'simbolo' => $m['simbolo'],
                    'es_predeterminada' => $m['es_predeterminada'],
                ]
            );
        }

        // 2. Sembrar tasas de cambio de USD a NIO para los últimos días
        $usdId = $monedasCreadas['USD']->id;
        $nioId = $monedasCreadas['NIO']->id;

        $tasas = [
            [
                'fecha' => now()->toDateString(),
                'moneda_origen_id' => $usdId,
                'moneda_destino_id' => $nioId,
                'tasa' => 36.5200,
                'es_fija' => true,
            ],
            [
                'fecha' => now()->subDay()->toDateString(),
                'moneda_origen_id' => $usdId,
                'moneda_destino_id' => $nioId,
                'tasa' => 36.5150,
                'es_fija' => false,
            ],
            [
                'fecha' => now()->subDays(2)->toDateString(),
                'moneda_origen_id' => $usdId,
                'moneda_destino_id' => $nioId,
                'tasa' => 36.5000,
                'es_fija' => false,
            ],
        ];

        foreach ($tasas as $t) {
            TasaCambio::updateOrCreate(
                [
                    'fecha' => $t['fecha'],
                    'moneda_origen_id' => $t['moneda_origen_id'],
                    'moneda_destino_id' => $t['moneda_destino_id'],
                ],
                [
                    'tasa' => $t['tasa'],
                    'es_fija' => $t['es_fija'],
                ]
            );
        }
    }
}
