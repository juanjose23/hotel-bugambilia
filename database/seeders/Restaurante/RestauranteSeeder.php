<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante;

use Database\Seeders\Restaurante\Cocina\ProcesoCocinaSeeder;
use Database\Seeders\Restaurante\Cocina\RecetaTransformacionMateriaPrimaSeeder;
use Database\Seeders\Restaurante\Cocina\SustitucionIngredienteSeeder;
use Database\Seeders\Restaurante\Cocina\TransformacionMateriaPrimaSeeder;
use Database\Seeders\Restaurante\Delivery\ZonaDeliverySeeder;
use Database\Seeders\Restaurante\Menu\MenuRestauranteSeeder;
use Database\Seeders\Restaurante\Pedidos\PedidoDemoSeeder;
use Database\Seeders\Restaurante\Stock\RestauranteStockBebidasProductosSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquesta la siembra del dominio Restaurante.
 *
 * El espacio principal (REST-001) y las mesas (MESA-01..06) los crea la suite
 * de Espacios (EspacioDefinicionSeeder); aquí solo se siembran configuraciones
 * propias del restaurante: menú, cocina, zonas de delivery y pedidos demo.
 */
final class RestauranteSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MenuRestauranteSeeder::class,
            RecetaTransformacionMateriaPrimaSeeder::class,
            TransformacionMateriaPrimaSeeder::class,
            ProcesoCocinaSeeder::class,
            ZonaDeliverySeeder::class,
            PedidoDemoSeeder::class,
            SustitucionIngredienteSeeder::class,
            RestauranteStockBebidasProductosSeeder::class,
        ]);

        $this->command->info('Módulo Restaurante: menú, cocina, zonas de delivery y pedidos demo sembrados.');
    }
}
