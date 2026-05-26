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
        Schema::create('espacios', function (Blueprint $table) {
            $table->comment('Áreas comunes alquilables del hotel (restaurantes, gimnasios, salones de eventos, etc.)');
            $table->id()->comment('Identificador único autoincremental del espacio');
            $table->string('codigo', 30)->unique()->comment('Código único del espacio (ej. ESP-2026-0001)');
            $table->string('nombre', 150)->comment('Nombre descriptivo del espacio (ej. Restaurante Los Jardines)');
            $table->text('descripcion')->nullable()->comment('Descripción general del espacio');
            $table->foreignId('tipo_espacio_id')->comment('Tipo de espacio (FK a catalogos, filtrado por TIPO_ESPACIO)')->constrained('catalogos');
            $table->foreignId('ubicacion_id')->nullable()->comment('Ubicación física del espacio dentro del hotel')->constrained('ubicaciones')->nullOnDelete();
            $table->integer('capacidad')->nullable()->comment('Capacidad máxima de personas');
            $table->string('horario', 100)->nullable()->comment('Horario de operación del espacio');
            $table->integer('estado')->default(1)->comment('0=Inactivo, 1=Activo, 2=Mantenimiento');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_espacio_id');
            $table->index('ubicacion_id');
            $table->index('estado');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE espacios ADD CONSTRAINT chk_estado_espacios CHECK (estado IN (0, 1, 2))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('espacios');
    }
};
