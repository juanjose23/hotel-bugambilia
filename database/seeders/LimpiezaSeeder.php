<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LimpiezaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LimpiezaTurnoSeeder::class,
            LimpiezaEjecucionSeeder::class,
            LimpiezaStockSeeder::class,
        ]);
    }
}
