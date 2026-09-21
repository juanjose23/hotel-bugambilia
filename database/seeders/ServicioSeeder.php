<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Servicios\ServicioCatalogoSeeder;
use Database\Seeders\Servicios\ServicioConsumibleStockSeeder;
use Database\Seeders\Servicios\ServicioDefinicionSeeder;
use Database\Seeders\Servicios\ServicioPoliticaSeeder;
use Database\Seeders\Servicios\ServicioPrecioHistoricoSeeder;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    /**
     * Orquesta la siembra modular y completa de servicios hoteleros.
     */
    public function run(): void
    {
        $this->call([
            ServicioCatalogoSeeder::class,
            ServicioPoliticaSeeder::class,
            ServicioDefinicionSeeder::class,
            ServicioPrecioHistoricoSeeder::class,
            ServicioConsumibleStockSeeder::class,
        ]);

        $this->command->info('Suite modular de Servicios, Histórico de Precios, Consumibles, Políticas e Imágenes sembrada exitosamente.');
    }
}
