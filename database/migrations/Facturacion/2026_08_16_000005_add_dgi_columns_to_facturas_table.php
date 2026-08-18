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
        Schema::table('facturas', function (Blueprint $table): void {
            $table->string('numero_autorizacion_dgi', 100)->nullable()->after('factura_autorizacion_dgi_id');
            $table->unsignedBigInteger('rango_autorizado_desde')->nullable()->after('numero_autorizacion_dgi');
            $table->unsignedBigInteger('rango_autorizado_hasta')->nullable()->after('rango_autorizado_desde');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE facturas
                SET numero_autorizacion_dgi = meta_datos->>'numero_autorizacion_dgi',
                    rango_autorizado_desde = (meta_datos->'rango_autorizado'->>'desde')::bigint,
                    rango_autorizado_hasta = (meta_datos->'rango_autorizado'->>'hasta')::bigint
                WHERE meta_datos IS NOT NULL AND meta_datos->>'numero_autorizacion_dgi' IS NOT NULL
            ");
        }

        Schema::table('facturas', function (Blueprint $table): void {
            $table->dropColumn('meta_datos');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table): void {
            $table->jsonb('meta_datos')->nullable()->after('datos_receptor');
            $table->dropColumn('numero_autorizacion_dgi');
            $table->dropColumn('rango_autorizado_desde');
            $table->dropColumn('rango_autorizado_hasta');
        });
    }
};
