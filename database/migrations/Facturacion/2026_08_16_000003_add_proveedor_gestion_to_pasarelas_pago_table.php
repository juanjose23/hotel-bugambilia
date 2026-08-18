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
        Schema::table('pasarelas_pago', function (Blueprint $table): void {
            $table->string('proveedor', 50)->nullable()->after('modo_prueba');
            $table->string('gestion', 50)->nullable()->after('proveedor');
        });

        DB::statement("
            UPDATE pasarelas_pago
            SET proveedor = meta_datos->>'proveedor',
                gestion = meta_datos->>'gestion'
            WHERE meta_datos IS NOT NULL
        ");

        Schema::table('pasarelas_pago', function (Blueprint $table): void {
            $table->dropColumn('meta_datos');
        });
    }

    public function down(): void
    {
        Schema::table('pasarelas_pago', function (Blueprint $table): void {
            $table->jsonb('meta_datos')->nullable()->after('configuracion');
            $table->dropColumn('proveedor');
            $table->dropColumn('gestion');
        });
    }
};
