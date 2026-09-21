<?php

declare(strict_types=1);

namespace Database\Seeders\Activos;

use Database\Seeders\Activos\Gestion\ActivoFijoSeeder;
use Database\Seeders\Activos\PlanesMantenimiento\PlanMantenimientoSeeder;
use Database\Seeders\Activos\Prefijos\PrefijoCodigoSeeder;
use Database\Seeders\Activos\Recalificar\RecalificarProductosActivoFijoSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquesta la siembra del módulo Activos Fijos.
 *
 * Flujo: Recalificación de productos tipo=3 → Prefijos de códigos →
 * adquisición (solicitud→cotización→OC→recepción con individualización,
 * que genera inventario en inv_stock) → asignación a habitaciones/espacios →
 * Planes de mantenimiento e historial de tickets preventivos/correctivos.
 */
class ActivosSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RecalificarProductosActivoFijoSeeder::class,
            PrefijoCodigoSeeder::class,
            ActivoFijoSeeder::class,
            PlanMantenimientoSeeder::class,
        ]);

        $this->command->info('ActivosSeeder: productos recalificados, prefijos, flujo de activos fijos y planes de mantenimiento sembrados.');
    }
}
