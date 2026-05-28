<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitud_limpiezas', function (Blueprint $table) {
            $table->comment('Tabla que almacena las solicitudes de limpieza de las habitaciones');
            $table->id()->comment('Identificador único autoincremental de la solicitud de limpieza');
            $table->foreignId('habitacion_id')
                ->nullable()
                ->comment('Identificador de la habitación asociada a la solicitud de limpieza')
                ->constrained('habitaciones')
                ->cascadeOnDelete();
            $table->foreignId('personal_id')
                ->nullable()
                ->comment('Usuario (personal de limpieza) asignado a la solicitud')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('creador_id')
                ->nullable()
                ->comment('Usuario que solicitó o registró la limpieza')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('prioridad', 20)
                ->default('normal')
                ->comment('Prioridad de la limpieza (alta, normal, baja)');
            $table->string('estado', 30)
                ->default('pendiente')
                ->comment('Estado de la solicitud de limpieza (pendiente, en_progreso, completada)');
            $table->text('notas')
                ->nullable()
                ->comment('Notas o instrucciones adicionales para la limpieza');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_limpiezas');
    }
};
