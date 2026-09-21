<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Reservas\ServiciosAdicionalesHistoricoSeeder;
use Database\Seeders\Restaurante\Pedidos\PedidoHistoricoMasivoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder orquestador para generar volumen histórico masivo (5,000+ registros).
 *
 * Incluye:
 * - 3,000 Pedidos de restaurante (mesas, bar/bebidas, catering/eventos)
 * - 2,000 Cargos de servicios adicionales a reservas (spa, lavandería, minibar, room service, traslados)
 *
 * Ejecución independiente recomendada:
 *   php artisan db:seed --class=HistoricoMasivoSeeder
 */
final class HistoricoMasivoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('================================================================');
        $this->command->info('    INICIANDO GENERACIÓN DE HISTÓRICO MASIVO (5,000+ REGISTROS) ');
        $this->command->info('================================================================');
        $this->command->newLine();

        $inicio = microtime(true);

        // 1. Pedidos masivos de restaurante, bar y banquetes
        $this->command->info('▶ Paso 1/2: Generando 3,000 pedidos de restaurante, bar y banquetes...');
        $this->call(PedidoHistoricoMasivoSeeder::class);

        $this->command->newLine();

        // 2. Cargos de servicios adicionales en cuentas de reservas
        $this->command->info('▶ Paso 2/2: Generando 2,000 cargos de servicios adicionales en cuentas...');
        $this->call(ServiciosAdicionalesHistoricoSeeder::class);

        $tiempo = round(microtime(true) - $inicio, 2);

        $totalPedidos = DB::table('pedidos')->where('codigo', 'like', 'PDR-HST-%')->count();
        $totalItems = DB::table('pedido_items')
            ->join('pedidos', 'pedidos.id', '=', 'pedido_items.pedido_id')
            ->where('pedidos.codigo', 'like', 'PDR-HST-%')
            ->count();
        $totalCargosServicios = DB::table('cuenta_detalles')
            ->whereIn('tipo_detalle', [5, 6, 7, 8, 9])
            ->count();

        $this->command->newLine();
        $this->command->info('================================================================');
        $this->command->info("    ✓ HISTÓRICO GENERADO EXITOSAMENTE EN {$tiempo}s            ");
        $this->command->info("    • Pedidos históricos:          {$totalPedidos}              ");
        $this->command->info("    • Ítems de pedidos:            {$totalItems}                ");
        $this->command->info("    • Cargos adicionales cuentas:  {$totalCargosServicios}      ");
        $this->command->info('================================================================');
        $this->command->newLine();
    }
}
