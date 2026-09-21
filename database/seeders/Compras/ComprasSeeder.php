<?php

declare(strict_types=1);

namespace Database\Seeders\Compras;

use Database\Seeders\Compras\Cotizaciones\CotizacionesSeeder;
use Database\Seeders\Compras\Devoluciones\DevolucionSeeder;
use Database\Seeders\Compras\OrdenesCompra\OrdenesCompraSeeder;
use Database\Seeders\Compras\Proveedores\ProveedorSeeder;
use Database\Seeders\Compras\Recepciones\RecepcionesSeeder;
use Database\Seeders\Compras\Solicitudes\SolicitudesSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquesta la siembra modular del módulo Compras.
 *
 * Flujo: Proveedores → Solicitudes → Cotizaciones → Órdenes de Compra →
 * Recepciones (que generan inventario) → Devoluciones.
 */
class ComprasSeeder extends Seeder
{
    public function run(): void
    {
        app(ProveedorSeeder::class)->ejecutar();

        $ctx = app(SolicitudesSeeder::class)->ejecutar();
        $ctx = app(CotizacionesSeeder::class)->ejecutar($ctx);
        $ctx = app(OrdenesCompraSeeder::class)->ejecutar($ctx);
        app(RecepcionesSeeder::class)->ejecutar($ctx);

        app(DevolucionSeeder::class)->ejecutar();

        $this->command->info('ComprasSeeder: proveedores, solicitudes, cotizaciones, órdenes, recepciones y devoluciones sembrados.');
    }
}
