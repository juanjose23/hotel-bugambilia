<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Reservas\FacturacionBorradorYVentasSeeder;
use Database\Seeders\Reservas\ReservaEspacioSeeder;
use Database\Seeders\Reservas\ReservaExcepcionSeeder;
use Database\Seeders\Reservas\ReservaFuturaSeeder;
use Database\Seeders\Reservas\ReservaHistoricaSeeder;
use Database\Seeders\Reservas\ReservaInHouseSeeder;
use Database\Seeders\Reservas\ReservaRestauranteSeeder;
use Database\Seeders\Reservas\ReservaServicioPaqueteSeeder;
use Illuminate\Database\Seeder;

/**
 * Orquestador maestro para la siembra modular y completa del ciclo de vida de Reservas,
 * Estancias, Cuentas/Folios, Cobros/Pagos y Facturación Fiscal DGI.
 */
class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ReservaHistoricaSeeder::class,        // 144 reservas pasadas (12 meses) con estancias, folios, cobros y facturas DGI
            ReservaInHouseSeeder::class,          // Huéspedes activos hoy con check-in y cuentas abiertas
            ReservaFuturaSeeder::class,           // 40 reservas futuras confirmadas con depósito y pendientes
            ReservaExcepcionSeeder::class,        // Cancelaciones y No-Show con bitácora
            ReservaRestauranteSeeder::class,      // 20 reservas de mesas/restaurante
            ReservaEspacioSeeder::class,          // 10 reservas de salón de eventos corporativos B2B
            ReservaServicioPaqueteSeeder::class,  // 15 reservas de paquetes híbridos y spa
            FacturacionBorradorYVentasSeeder::class, // Facturas en borrador, anuladas y ventas POS
        ]);

        $this->command->info('Suite modular de Reservas, Estancias, Pagos, Ventas y Facturación sembrada exitosamente.');
    }
}
