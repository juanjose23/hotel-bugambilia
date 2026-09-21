<?php

namespace Database\Seeders;

use Database\Seeders\Activos\ActivosSeeder;
use Database\Seeders\Clientes\BeneficioClienteSeeder;
use Database\Seeders\Clientes\ClientesDemoSeeder;
use Database\Seeders\Clientes\ClientesMasivoSeeder;
use Database\Seeders\Colaboradores\ColaboradorBaseSeeder;
use Database\Seeders\Colaboradores\ColaboradorLaboralSeeder;
use Database\Seeders\Colaboradores\ColaboradorSaludSeeder;
use Database\Seeders\Compras\ComprasSeeder;
use Database\Seeders\Configuracion\CatalogoSeeder;
use Database\Seeders\Configuracion\CatalogoTipoSeeder;
use Database\Seeders\Configuracion\FacturaFiscalSeeder;
use Database\Seeders\Configuracion\MonedaSeeder;
use Database\Seeders\Configuracion\PaisSeeder;
use Database\Seeders\Configuracion\UbicacionSeeder;
use Database\Seeders\Inventario\InventarioSeeder;
use Database\Seeders\Limpieza\LimpiezaSeeder;
use Database\Seeders\Restaurante\RestauranteSeeder;
use Database\Seeders\Servicios\ServicioActivoAsignacionSeeder;
use Database\Seeders\Usuarios\RolAdministracionSeeder;
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
        // ─── 01. CORE, CATÁLOGOS BASE & USUARIOS ADMIN ───
        $this->call(PaisSeeder::class);
        $this->call(MonedaSeeder::class);
        $this->call(UsuarioAdminSeeder::class);
        $this->call(RolAdministracionSeeder::class);
        $this->call(CatalogoTipoSeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(UbicacionSeeder::class);
        $this->call(TasaCambioSeeder::class);
        $this->call(PasarelaPagoSeeder::class);

        // ─── CONFIGURACIÓN FISCAL DGI (NICARAGUA) ───
        $this->call(FacturaFiscalSeeder::class);

        // ─── 02. GOBERNANZA & PERSONAL (COLABORADORES) ───
        $this->call(ColaboradorBaseSeeder::class);
        $this->call(ColaboradorSaludSeeder::class);
        $this->call(ColaboradorLaboralSeeder::class);

        // ─── 03. COMPRAS, BODEGA & INVENTARIO ───
        $this->call(ProductoSeeder::class);
        $this->call(ComprasSeeder::class);
        $this->call(InventarioSeeder::class);

        // ─── 04. SERVICIOS ───
        $this->call(ServicioSeeder::class);

        // ─── 05. INFRAESTRUCTURA (HABITACIONES, ESPACIOS & ACTIVOS FIJOS) ───
        $this->call(HabitacionSeeder::class);
        $this->call(EspacioSeeder::class);
        $this->call(ActivosSeeder::class);

        // Flota de activos fijos asignados a servicios (requiere ActivosSeeder)
        $this->call(ServicioActivoAsignacionSeeder::class);

        $this->call(KitSeeder::class);

        // ─── 06. RESTAURANTE, MESAS, MENÚ, COCINA & ZONAS ───
        $this->call(RestauranteSeeder::class);

        // ─── 07. PROMOCIONES & PAQUETES TURÍSTICOS ───
        $this->call(PromocionSeeder::class);

        // ─── 08. CLIENTES & USUARIOS PORTAL ───
        $this->call(ClientesDemoSeeder::class);
        $this->call(ClientesMasivoSeeder::class);

        // ─── 09. OPERACIÓN HOTELERA, RESERVAS, PAGOS Y FACTURACIÓN DGI ───
        $this->call(ReservaSeeder::class);

        // ─── 10. BENEFICIOS Y FIDELIZACIÓN DE CLIENTES ───
        $this->call(BeneficioClienteSeeder::class);

        // ─── 11. HOUSEKEEPING, LIMPIEZA & MANTENIMIENTO ───
        $this->call(LimpiezaSeeder::class);
        $this->call(MantenimientoCasosUsoSeeder::class);
    }
}
