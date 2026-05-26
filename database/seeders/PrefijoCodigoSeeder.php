<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Activos\PrefijoCodigo;
use Illuminate\Database\Seeder;

class PrefijoCodigoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prefijos = [
            ['prefijo' => 'TV', 'ultimo_numero' => 0],
            ['prefijo' => 'AC', 'ultimo_numero' => 0],
            ['prefijo' => 'CAM', 'ultimo_numero' => 0],
            ['prefijo' => 'ACT', 'ultimo_numero' => 0],
        ];

        foreach ($prefijos as $p) {
            PrefijoCodigo::updateOrCreate(
                ['prefijo' => $p['prefijo']],
                ['ultimo_numero' => $p['ultimo_numero']]
            );
        }
    }
}
