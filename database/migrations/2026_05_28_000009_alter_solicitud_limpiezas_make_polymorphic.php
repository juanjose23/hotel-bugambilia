<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitud_limpiezas', function (Blueprint $table) {
            $table->string('limpiable_type')
                ->after('id')
                ->comment('Modelo asociado a la limpieza (App\Models\Habitaciones\Habitacion o App\Models\Espacios\Espacio)');
            $table->unsignedBigInteger('limpiable_id')
                ->after('limpiable_type')
                ->comment('Identificador único del modelo a limpiar');

            $table->index(['limpiable_type', 'limpiable_id'], 'idx_solicitud_limpieza_limpiable');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('solicitud_limpiezas', function (Blueprint $table) {
                $table->dropForeign(['habitacion_id']);
                $table->dropColumn('habitacion_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('solicitud_limpiezas', function (Blueprint $table) {
            $table->dropIndex('idx_solicitud_limpieza_limpiable');
            $table->dropColumn(['limpiable_type', 'limpiable_id']);

            $table->foreignId('habitacion_id')
                ->after('id')
                ->comment('Identificador de la habitación asociada a la solicitud de limpieza')
                ->constrained('habitaciones')
                ->cascadeOnDelete();
        });
    }
};
