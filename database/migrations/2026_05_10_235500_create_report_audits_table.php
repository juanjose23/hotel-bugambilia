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
            $table->id();
            $table->foreignId('usuario_id')->nullable()->comment('Usuario que generó el reporte (luego renombrado a usuario_id)')->constrained('users')->nullOnDelete();
            $table->string('tipo_reporte')->comment('Código del reporte (ej. HTB-CP-001, luego renombrado a tipo_reporte)');
            $table->json('parametros')->nullable()->comment('Filtros aplicados en JSON (luego renombrado a parametros)');
            $table->string('ruta_archivo')->nullable()->comment('Ruta al archivo generado (luego renombrado a ruta_archivo)');
            $table->integer('conteo_descargas')->default(0)->comment('Contador de descargas (luego renombrado a conteo_descargas)');
            $table->timestamp('ultima_descarga_en')->nullable()->comment('Fecha de última descarga (luego renombrado a ultima_descarga_en)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_reportes');
    }
};
