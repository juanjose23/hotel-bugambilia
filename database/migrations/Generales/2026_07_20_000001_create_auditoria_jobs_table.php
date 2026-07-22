<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_jobs', function (Blueprint $table) {
            $table->comment('Registro de auditoría de ejecución de jobs del sistema.');
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo_job')->comment('Identificador del tipo de job ejecutado');
            $table->string('nombre_job')->comment('Nombre legible del job');
            $table->string('tipo_ejecucion')->default('manual')->comment('manual o automatico');
            $table->string('estado')->default('pendiente')->comment('pendiente, ejecutando, completado, fallido');
            $table->text('mensaje')->nullable()->comment('Resultado o mensaje de error');
            $table->timestamp('ejecutado_en')->nullable()->comment('Momento en que inició la ejecución');
            $table->timestamp('completado_en')->nullable()->comment('Momento en que finalizó la ejecución');
            $table->timestamps();
            $table->index('tipo_job');
            $table->index('estado');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_jobs');
    }
};
