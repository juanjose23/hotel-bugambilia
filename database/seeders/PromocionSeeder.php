<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Promociones\PromocionConsumibleStockSeeder;
use Database\Seeders\Promociones\PromocionDefinicionSeeder;
use Database\Seeders\Promociones\PromocionPoliticaSeeder;
use Database\Seeders\Promociones\PromocionPrecioHistoricoSeeder;
use Illuminate\Database\Seeder;

class PromocionSeeder extends Seeder
{
    /**
     * Orquesta la siembra modular y completa de promociones, paquetes hoteleros,
     * galería de imágenes, histórico de precios, stock de consumibles y políticas.
     */
    public function run(): void
    {
        $this->call([
            PromocionPoliticaSeeder::class,
            PromocionDefinicionSeeder::class,
            PromocionPrecioHistoricoSeeder::class,
            PromocionConsumibleStockSeeder::class,
        ]);

        $this->command->info('Suite modular de Promociones, Paquetes, Galería, Precios, Stock y Políticas sembrada exitosamente.');
    }
}
