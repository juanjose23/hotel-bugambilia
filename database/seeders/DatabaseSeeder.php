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
        // Base del sistema
        $this->call(PaisSeeder::class);
        $this->call(CatalogoTipoSeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(UbicacionSeeder::class);
        $this->call(TasaCambioSeeder::class);

        // Colaboradores
        $this->call(ColaboradorBaseSeeder::class);
        $this->call(ColaboradorSaludSeeder::class);
        $this->call(ColaboradorLaboralSeeder::class);

        // Maestros de compras e inventario
        $this->call(ProveedorSeeder::class);
        $this->call(ProductoSeeder::class);
        $this->call(RecalificarProductosActivoFijoSeeder::class); // tipo=3 para CAT_MOB y CAT_ELECTRO
        $this->call(PrefijoCodigoSeeder::class);

        // Flujo transaccional de compras
        $this->call(ProcurementFlowSeeder::class);

        // Activos Fijos: productos y flujo de compra completo
        $this->call(ActivoFijoSeeder::class);

        // Servicios y tarifas
        $this->call(ServicioSeeder::class);

        // Habitaciones, detalles, precios y políticas
        $this->call(HabitacionSeeder::class);

        // Datos demo de inventario y devoluciones
        $this->call(InventarioSeeder::class);
        $this->call(DevolucionSeeder::class);

    }
}
