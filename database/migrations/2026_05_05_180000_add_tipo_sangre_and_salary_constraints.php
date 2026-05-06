<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('colaborador_datos_medicos', function (Blueprint $table) {
            if (!Schema::hasColumn('colaborador_datos_medicos', 'tipo_sangre')) {
                $table->string('tipo_sangre', 3)->nullable()->after('peso');
            }
        });
        DB::statement("
        CREATE UNIQUE INDEX unique_active_salary_per_colaborador
        ON colaborador_salarios (colaborador_id)
        WHERE estado = 1
    ");
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

        DB::statement('DROP INDEX unique_active_salary_per_colaborador ON colaborador_salarios');
    }
};