<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Habitaciones\HabitacionPoliticaSeeder;
use Database\Seeders\Habitaciones\HabitacionSeeder as HabitacionesModuloSeeder;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
    /**
     * Orquesta la siembra modular y completa de las 122 habitaciones y sus relaciones.
     */
    public function run(): void
    {
        $this->call([
            HabitacionPoliticaSeeder::class,
            HabitacionesModuloSeeder::class,
        ]);

        $this->command->info('Suite modular de 122 Habitaciones sembrada exitosamente con Políticas, Precios, Stock, Inventario Fijo, Servicios e Imágenes.');
    }
}
