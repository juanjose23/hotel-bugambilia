<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas nativas de Laravel para el sistema de colas (queue).
     * jobs: cola de trabajos pendientes.
     * job_batches: lotes de trabajos (batch processing).
     * failed_jobs: registro de trabajos fallidos para depuración.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->comment('Tabla nativa de Laravel que actúa como cola de trabajos pendientes para ejecución asíncrona.');
            $table->id()->comment('Identificador único autoincremental del trabajo en cola');
            $table->string('queue')->index()->comment('Nombre de la cola (ej. default, emails, reports)');
            $table->longText('payload')->comment('Datos serializados del trabajo a ejecutar');
            $table->unsignedSmallInteger('attempts')->comment('Número de intentos realizados');
            $table->unsignedInteger('reserved_at')->nullable()->comment('Timestamp UNIX cuando un worker reservó el job');
            $table->unsignedInteger('available_at')->comment('Timestamp UNIX a partir del cual está disponible');
            $table->unsignedInteger('created_at')->comment('Timestamp UNIX de creación del registro');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->comment('Tabla nativa de Laravel para la gestión y seguimiento de ejecución de lotes de trabajos en paralelo.');
            $table->string('id')->primary()->comment('Identificador único en formato UUID del lote de trabajos');
            $table->string('name')->comment('Nombre descriptivo del lote');
            $table->integer('total_jobs')->comment('Total de trabajos en el lote');
            $table->integer('pending_jobs')->comment('Trabajos pendientes por ejecutar');
            $table->integer('failed_jobs')->comment('Trabajos que fallaron');
            $table->longText('failed_job_ids')->comment('IDs de los trabajos fallidos en JSON');
            $table->mediumText('options')->nullable()->comment('Opciones adicionales del lote (en JSON)');
            $table->integer('cancelled_at')->nullable()->comment('Timestamp UNIX de cancelación del lote');
            $table->integer('created_at')->comment('Timestamp UNIX de creación del lote');
            $table->integer('finished_at')->nullable()->comment('Timestamp UNIX de finalización del lote');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->comment('Tabla nativa de Laravel para el registro histórico y depuración de trabajos en cola que fallaron.');
            $table->id()->comment('Identificador único autoincremental del trabajo fallido');
            $table->string('uuid')->unique()->comment('Identificador único global (UUID) del trabajo');
            $table->text('connection')->comment('Conexión de cola usada');
            $table->text('queue')->comment('Nombre de la cola');
            $table->longText('payload')->comment('Datos del trabajo al momento del fallo');
            $table->longText('exception')->comment('Mensaje y trace de la excepción');
            $table->timestamp('failed_at')->useCurrent()->comment('Fecha y hora exacta en la que ocurrió el fallo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
