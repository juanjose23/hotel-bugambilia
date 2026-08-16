<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'pedidos',
        'cuentas',
        'ventas',
        'facturas',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ($this->tables as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'cliente_id')) {
                    continue;
                }

                DB::statement(<<<SQL
                    UPDATE {$table}
                    SET cliente_id = clientes.id
                    FROM clientes
                    WHERE {$table}.cliente_id IS NOT NULL
                        AND clientes.persona_id = {$table}.cliente_id
                SQL);
            }
        });

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'cliente_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->dropForeign(['cliente_id']);
                $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach ($this->tables as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'cliente_id')) {
                    continue;
                }

                DB::statement(<<<SQL
                    UPDATE {$table}
                    SET cliente_id = clientes.persona_id
                    FROM clientes
                    WHERE {$table}.cliente_id IS NOT NULL
                        AND clientes.id = {$table}.cliente_id
                SQL);
            }
        });

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'cliente_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->dropForeign(['cliente_id']);
                $table->foreign('cliente_id')->references('id')->on('personas')->nullOnDelete();
            });
        }
    }
};
