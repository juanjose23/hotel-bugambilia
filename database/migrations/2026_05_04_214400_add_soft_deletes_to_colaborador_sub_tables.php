<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega soft deletes a las subtablas de colaboradores que
     * originalmente no fueron creadas con eliminación suave.
     * Permite mantener integridad histórica al eliminar registros
     * de datos médicos, contactos, salarios, cargos y documentos.
     */
    public function up(): void
    {
        Schema::table('colaborador_datos_medicos', function (Blueprint $table) {
            $table->softDeletes()->comment('Marca de tiempo de eliminación suave');
        });

        Schema::table('colaborador_contactos_emergencia', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('colaborador_salarios', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('colaborador_cargos_historial', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('colaborador_documentos', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colaborador_datos_medicos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('colaborador_contactos_emergencia', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('colaborador_salarios', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('colaborador_cargos_historial', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('colaborador_documentos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
