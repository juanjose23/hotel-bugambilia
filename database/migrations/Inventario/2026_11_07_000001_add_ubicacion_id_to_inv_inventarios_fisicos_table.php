<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_inventarios_fisicos', function (Blueprint $table) {
            $table->foreignId('ubicacion_id')
                ->nullable()
                ->after('creado_por_id')
                ->comment('Ubicación o almacén auditado (null = todo el hotel)')
                ->constrained('ubicaciones')
                ->nullOnDelete();

            $table->index('ubicacion_id');
        });
    }

    public function down(): void
    {
        Schema::table('inv_inventarios_fisicos', function (Blueprint $table) {
            $table->dropForeign(['ubicacion_id']);
            $table->dropIndex(['ubicacion_id']);
            $table->dropColumn('ubicacion_id');
        });
    }
};
