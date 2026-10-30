<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Restaurante\Historico\MermaCocinaHistoricoMasivoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Orquestador del histórico masivo de merma de cocina.
 *
 * Garantiza que exista la base (productos, menú, cocina) y luego genera el
 * histórico de un año de operación de restaurante: lotes en Cocina Restaurante,
 * pedidos, transformaciones, procesos y mermas por vencimiento.
 *
 * Uso:
 *   php artisan db:seed --class=HistoricoMermaCocinaSeeder
 *
 * Parámetros opcionales vía env:
 *   MERMA_COCINA_ESCALA=0.5  → reduce volúmenes de pedidos por día (default 1.0)
 */
final class HistoricoMermaCocinaSeeder extends Seeder
{
    public function run(): void
    {
        set_time_limit(0);

        $productos = DB::table('productos')->count();
        $platos = DB::table('platos')->count();

        if ($productos === 0 || $platos === 0) {
            $this->command->info('Base de datos vacía: sembrando la base del hotel...');
            $this->call(DatabaseSeeder::class);
        }

        $this->command->info('Generando histórico masivo de merma de cocina (365 días)...');

        $inicio = microtime(true);

        $this->call(MermaCocinaHistoricoMasivoSeeder::class);

        $segundos = (int) (microtime(true) - $inicio);

        $this->reportarTamano($segundos);
    }

    private function reportarTamano(int $segundos): void
    {
        $this->command->info("Histórico de cocina finalizado en {$segundos}s.");

        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $db = DB::select('SELECT pg_size_pretty(pg_database_size(current_database())) AS db_size');

        $this->command->info('Tamaño total de la base de datos: '.($db[0]->db_size ?? '?'));
    }
}
