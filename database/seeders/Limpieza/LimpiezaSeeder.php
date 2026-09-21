<?php

declare(strict_types=1);

namespace Database\Seeders\Limpieza;

use Database\Seeders\Limpieza\Carrito\LimpiezaCarritoStockSeeder;
use Database\Seeders\Limpieza\Ejecucion\LimpiezaEjecucionSeeder;
use Database\Seeders\Limpieza\Lavanderia\LimpiezaLavanderiaSeeder;
use Database\Seeders\Limpieza\Stock\LimpiezaStockHabitacionSeeder;
use Database\Seeders\Limpieza\Turno\LimpiezaTurnoSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquestador del módulo Limpieza.
 *
 * Secuencia el seeding según la lógica de los interactores: primero turnos y
 * carritos, luego su stock (carritos y habitaciones) y lavandería, y por
 * último las ejecuciones de limpieza demo que consumen esos datos.
 */
final class LimpiezaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LimpiezaTurnoSeeder::class);
        $this->call(LimpiezaCarritoStockSeeder::class);
        $this->call(LimpiezaStockHabitacionSeeder::class);
        $this->call(LimpiezaLavanderiaSeeder::class);
        $this->call(LimpiezaEjecucionSeeder::class);
    }
}
