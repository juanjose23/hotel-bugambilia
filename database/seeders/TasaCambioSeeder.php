<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TasaCambioSeeder extends Seeder
{
    public function run(): void
    {
        $usd = Moneda::where('codigo', 'USD')->first();
        $nio = Moneda::where('codigo', 'NIO')->first();

        if (! $usd || ! $nio) {
            if ($this->command !== null) {
                $this->command->warn('Monedas USD y/o NIO no encontradas. Ejecute MonedaSeeder primero.');
            }

            return;
        }

        // Tasa fija USD → NIO
        TasaCambio::firstOrCreate(
            [
                'fecha' => Carbon::now()->toDateString(),
                'moneda_origen_id' => $usd->id,
                'moneda_destino_id' => $nio->id,
            ],
            [
                'tasa' => 36.5200,
                'es_fija' => true,
            ]
        );

        if ($this->command !== null) {
            $this->command->info('Tasa de cambio USD → NIO: 36.5200 creada.');
        }
    }
}
