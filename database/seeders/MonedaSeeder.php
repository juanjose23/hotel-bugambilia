<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Monedas\Moneda;
use Illuminate\Database\Seeder;

class MonedaSeeder extends Seeder
{
    public function run(): void
    {
        Moneda::firstOrCreate(
            ['codigo' => 'NIO'],
            [
                'nombre' => 'Córdoba Nicaragüense',
                'simbolo' => 'C$',
                'es_predeterminada' => true,
            ]
        );

        Moneda::firstOrCreate(
            ['codigo' => 'USD'],
            [
                'nombre' => 'Dólar Estadounidense',
                'simbolo' => '$',
                'es_predeterminada' => false,
            ]
        );

        if ($this->command !== null) {
            $this->command->info('Monedas NIO y USD creadas/verificadas.');
        }
    }
}
