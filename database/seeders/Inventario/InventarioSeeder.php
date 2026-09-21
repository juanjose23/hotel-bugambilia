<?php

declare(strict_types=1);

namespace Database\Seeders\Inventario;

use Database\Seeders\Inventario\Lotes\InventarioLotesSeeder;
use Database\Seeders\Inventario\Packs\KitSeeder;
use Database\Seeders\Inventario\StockInicial\StockInicialPackSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquesta la siembra modular del módulo Inventario.
 *
 * 1. KitSeeder: define los paquetes / packs operativos de blancos, toallas, amenidades y limpieza.
 * 2. InventarioLotesSeeder: garantiza inventario (lotes + stock + movimientos)
 *    para todo producto consumible del catálogo.
 * 3. StockInicialPackSeeder: lotes de stock inicial de los packs de amenidades,
 *    blancos y papelería dentro de la bodega.
 */
class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KitSeeder::class,
            InventarioLotesSeeder::class,
            StockInicialPackSeeder::class,
        ]);

        $this->command->info('InventarioSeeder: inventario completo de productos sembrado en el almacén.');
    }
}
