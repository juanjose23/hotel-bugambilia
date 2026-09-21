<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Productos\ProductoAbarroteSeeder;
use Database\Seeders\Productos\ProductoAlimentoNoPerecederoSeeder;
use Database\Seeders\Productos\ProductoAlimentoPerecederoSeeder;
use Database\Seeders\Productos\ProductoAmenidadBanoSeeder;
use Database\Seeders\Productos\ProductoAmenidadHabitacionSeeder;
use Database\Seeders\Productos\ProductoBebidaSeeder;
use Database\Seeders\Productos\ProductoBlancoOtroSeeder;
use Database\Seeders\Productos\ProductoBlancoSabanaSeeder;
use Database\Seeders\Productos\ProductoBlancoToallaSeeder;
use Database\Seeders\Productos\ProductoEquipoElectronicoSeeder;
use Database\Seeders\Productos\ProductoGeneralSeeder;
use Database\Seeders\Productos\ProductoHerramientaMantenimientoSeeder;
use Database\Seeders\Productos\ProductoLimpiezaHerramientaSeeder;
use Database\Seeders\Productos\ProductoLimpiezaQuimicoSeeder;
use Database\Seeders\Productos\ProductoMantenimientoLimpiezaSeeder;
use Database\Seeders\Productos\ProductoMobiliarioSeeder;
use Database\Seeders\Productos\ProductoUniformeSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquestador de productos por categoría.
 *
 * Cada categoría vive en su propio seeder (Database\Seeders\Productos),
 * que hereda de CreaProductosSeeder y mantiene la generación real de
 * productos, variantes y SKUs (idempotente).
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Alimentos y bebidas
        $this->call(ProductoAlimentoPerecederoSeeder::class);
        $this->call(ProductoAlimentoNoPerecederoSeeder::class);
        $this->call(ProductoAbarroteSeeder::class);
        $this->call(ProductoBebidaSeeder::class);

        // Amenidades
        $this->call(ProductoAmenidadBanoSeeder::class);
        $this->call(ProductoAmenidadHabitacionSeeder::class);

        // Limpieza y suministros
        $this->call(ProductoLimpiezaQuimicoSeeder::class);
        $this->call(ProductoLimpiezaHerramientaSeeder::class);
        $this->call(ProductoMantenimientoLimpiezaSeeder::class);

        // Blancos y uniformes
        $this->call(ProductoBlancoSabanaSeeder::class);
        $this->call(ProductoBlancoToallaSeeder::class);
        $this->call(ProductoBlancoOtroSeeder::class);
        $this->call(ProductoUniformeSeeder::class);

        // Activos fijos y equipos
        $this->call(ProductoMobiliarioSeeder::class);
        $this->call(ProductoEquipoElectronicoSeeder::class);
        $this->call(ProductoHerramientaMantenimientoSeeder::class);

        // Generales
        $this->call(ProductoGeneralSeeder::class);
    }
}
