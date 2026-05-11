<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración complementaria:
     * 1. Agrega columna tipo_sangre a datos_medicos si no existe.
     * 2. Crea índice único parcial para asegurar solo un salario activo
     *    por colaborador (unique_active_salary_per_colaborador).
     * Nota: El DROP INDEX aparece duplicado en down() por compatibilidad
     * con diferentes versiones de PostgreSQL/MySQL.
     */
    public function up(): void
    {
        Schema::table('colaborador_datos_medicos', function (Blueprint $table) {
            if (! Schema::hasColumn('colaborador_datos_medicos', 'tipo_sangre')) {
                $table->string('tipo_sangre', 3)->nullable()->after('peso')->comment('Tipo de sangre (A+, O-, AB+, etc.)');
            }
        });
        DB::statement('
        CREATE UNIQUE INDEX unique_active_salary_per_colaborador
        ON colaborador_salarios (colaborador_id)
        WHERE estado = 1
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colaborador_datos_medicos', function (Blueprint $table) {
            if (Schema::hasColumn('colaborador_datos_medicos', 'tipo_sangre')) {
                $table->dropColumn('tipo_sangre');
            }
        });
        DB::statement('DROP INDEX IF EXISTS unique_active_salary_per_colaborador');
    }
};
