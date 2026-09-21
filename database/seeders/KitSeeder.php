<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Inventario\Packs\KitSeeder as DomainKitSeeder;
use Illuminate\Database\Seeder;

/**
 * Delegador raíz hacia el seeder de dominio Database\Seeders\Inventario\Packs\KitSeeder.
 */
final class KitSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DomainKitSeeder::class);
    }
}
