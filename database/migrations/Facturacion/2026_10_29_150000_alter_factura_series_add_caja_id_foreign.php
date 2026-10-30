<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factura_series', function (Blueprint $table): void {
            $table->foreignId('caja_id')
                ->nullable()
                ->after('sucursal_codigo')
                ->constrained('tes_cajas')
                ->nullOnDelete();
        });

        if (Schema::hasColumn('factura_series', 'caja_codigo')) {
            DB::statement('
                UPDATE factura_series
                SET caja_id = tes_cajas.id
                FROM tes_cajas
                WHERE factura_series.caja_codigo IS NOT NULL
                  AND factura_series.caja_codigo = tes_cajas.codigo
            ');

            Schema::table('factura_series', function (Blueprint $table): void {
                $table->dropIndex(['caja_codigo']);
                $table->dropColumn('caja_codigo');
            });
        }
    }

    public function down(): void
    {
        Schema::table('factura_series', function (Blueprint $table): void {
            $table->string('caja_codigo', 30)->nullable()->index()->after('sucursal_codigo');
        });

        DB::statement('
            UPDATE factura_series
            SET caja_codigo = tes_cajas.codigo
            FROM tes_cajas
            WHERE factura_series.caja_id IS NOT NULL
              AND factura_series.caja_id = tes_cajas.id
        ');

        Schema::table('factura_series', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('caja_id');
        });
    }
};
