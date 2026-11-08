<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Restaurante\Historico\MermaCocinaHistoricoMasivoSeeder;
use Database\Seeders\Tesoreria\TesoreriaDemoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DemostracionCompletaSeeder extends Seeder
{
    public function run(): void
    {
        $conexion = DB::connection();
        if (
            ! app()->environment('local', 'testing')
            || $conexion->getDriverName() !== 'pgsql'
            || ! str_ends_with((string) $conexion->getDatabaseName(), '_demo')
        ) {
            throw new RuntimeException('La carga completa requiere una base PostgreSQL de demostración terminada en _demo y APP_ENV local o testing.');
        }

        set_time_limit(0);

        $conexion->transaction(function (): void {
            DB::statement('LOCK TABLE users IN SHARE ROW EXCLUSIVE MODE');

            if (DB::table('users')->exists() || DB::table('productos')->exists()) {
                throw new RuntimeException('La carga completa requiere una base de demostración recién migrada y vacía.');
            }

            $this->call([
                DatabaseSeeder::class,
                HistoricoComprasInventarioActivosSeeder::class,
                MermaCocinaHistoricoMasivoSeeder::class,
                TesoreriaDemoSeeder::class,
            ]);
        });
    }
}
