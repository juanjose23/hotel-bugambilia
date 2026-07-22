<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración inicial del sistema de auditoría de reportes.
     * Ver auditoria_reportes para la versión actual.
     */
    public function up(): void
    {

        Schema::create('auditoria_reportes', function (Blueprint $table) {
            $table->comment('Tabla para auditar y registrar la generación, parámetros y descargas de reportes dentro del sistema.');
            $table->id()->comment('Identificador único autoincremental del registro de auditoría de reportes');
            $table->foreignId('usuario_id')->nullable()->comment('Usuario que generó el reporte (luego renombrado a usuario_id)')->constrained('users')->nullOnDelete();
            $table->string('tipo_reporte')->comment('Código del reporte (ej. HTB-CP-001, luego renombrado a tipo_reporte)');
            $table->json('parametros')->nullable()->comment('Filtros aplicados en JSON (luego renombrado a parametros)');
            $table->string('ruta_archivo')->nullable()->comment('Ruta al archivo generado (luego renombrado a ruta_archivo)');
            $table->timestamps();
            $table->softDeletes();
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auditoria_reportes', fn (Blueprint $t) => $t->dropIndex(['usuario_id']));
        Schema::dropIfExists('auditoria_reportes');
    }
};
