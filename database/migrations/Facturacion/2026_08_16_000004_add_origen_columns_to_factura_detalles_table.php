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
        Schema::table('factura_detalles', function (Blueprint $table): void {
            $table->string('origen_type', 100)->nullable()->after('venta_detalle_id');
            $table->unsignedBigInteger('origen_id')->nullable()->after('origen_type');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE factura_detalles
                SET origen_type = meta_datos->>'origen_type',
                    origen_id = (meta_datos->>'origen_id')::bigint
                WHERE meta_datos IS NOT NULL AND meta_datos->>'origen_id' IS NOT NULL
            ");
        }

        Schema::table('factura_detalles', function (Blueprint $table): void {
            $table->dropColumn('meta_datos');
        });
    }

    public function down(): void
    {
        Schema::table('factura_detalles', function (Blueprint $table): void {
            $table->jsonb('meta_datos')->nullable()->after('total_linea');
            $table->dropColumn('origen_type');
            $table->dropColumn('origen_id');
        });
    }
};
