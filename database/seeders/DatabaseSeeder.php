<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PaisSeeder::class);
        $this->call(CatalogoTipoSeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(UbicacionSeeder::class);
        $this->call(ColaboradorBaseSeeder::class);
        $this->call(ColaboradorSaludSeeder::class);
        $this->call(ColaboradorLaboralSeeder::class);
        $this->call(ProductoSeeder::class);
        $this->call(ProveedorSeeder::class);
        $this->call(ProcurementFlowSeeder::class);
    }
}
