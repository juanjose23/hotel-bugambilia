<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Espacios\EspacioConsumibleStockSeeder;
use Database\Seeders\Espacios\EspacioDefinicionSeeder;
use Database\Seeders\Espacios\EspacioPoliticaSeeder;
use Database\Seeders\Espacios\EspacioPrecioHistoricoSeeder;
use Illuminate\Database\Seeder;

class EspacioSeeder extends Seeder
{
    /**
     * Orquesta la siembra modular y completa de espacios, sub-espacios (mesas),
     * galería de imágenes, histórico de precios, stock de consumibles, servicios y políticas.
     */
    public function run(): void
    {
        $this->call([
            EspacioPoliticaSeeder::class,
            EspacioDefinicionSeeder::class,
            EspacioPrecioHistoricoSeeder::class,
            EspacioConsumibleStockSeeder::class,
        ]);

        $this->command->info('Suite modular de Espacios, Sub-espacios, Galería, Precios, Stock, Servicios y Políticas sembrada exitosamente.');
    }
}
