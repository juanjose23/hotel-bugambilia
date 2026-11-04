<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ResetPostgresSequencesCommand extends Command
{
    protected $signature = 'db:reset-sequences';

    protected $description = 'Sincroniza y reajusta todas las secuencias primarias de PostgreSQL al valor máximo existente';

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'pgsql') {
            $this->info("La conexión actual es [{$driver}], no requiere reajuste de secuencias de PostgreSQL.");

            return self::SUCCESS;
        }

        $this->info('Sincronizando secuencias en PostgreSQL...');

        /** @var array<object{table_name: string, column_name: string}> $columns */
        $columns = DB::select("
            SELECT table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND (column_default LIKE 'nextval(%' OR is_identity = 'YES')
            ORDER BY table_name, column_name
        ");

        $sincronizadas = 0;

        foreach ($columns as $col) {
            $table = $col->table_name;
            $column = $col->column_name;

            try {
                /** @var object{seq: string|null}|null $seqObj */
                $seqObj = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS seq', [$table, $column]);
                $seq = $seqObj !== null ? $seqObj->seq : null;

                if (! empty($seq)) {
                    /** @var object{max_val: int|string|null}|null $maxObj */
                    $maxObj = DB::selectOne("SELECT COALESCE(MAX(\"{$column}\"), 0) AS max_val FROM \"{$table}\"");
                    $max = $maxObj !== null ? (int) $maxObj->max_val : 0;

                    if ($max > 0) {
                        DB::statement('SELECT setval(?, ?, true)', [$seq, $max]);
                    } else {
                        DB::statement('SELECT setval(?, 1, false)', [$seq]);
                    }

                    $this->line("  ✓ {$table}.{$column} -> secuencia sincronizada en {$max}");
                    $sincronizadas++;
                }
            } catch (Throwable $e) {
                $this->warn("  ⚠ No se pudo sincronizar {$table}.{$column}: {$e->getMessage()}");
            }
        }

        $this->info("¡Completado! {$sincronizadas} secuencias de PostgreSQL fueron sincronizadas exitosamente.");

        return self::SUCCESS;
    }
}
