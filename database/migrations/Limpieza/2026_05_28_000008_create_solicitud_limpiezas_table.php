<?php

declare(strict_types=1);

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
            $table->string('limpiable_type')->after('id')->comment('Modelo asociado a la limpieza (App\Models\Habitaciones\Habitacion o App\Models\Espacios\Espacio)');
            $table->unsignedBigInteger('limpiable_id')->after('limpiable_type')->comment('Identificador único del modelo a limpiar');
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
            $table->integer('estado')->default(1);
            $table->text('notas')
                ->nullable()
                ->comment('Notas o instrucciones adicionales para la limpieza');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['limpiable_type', 'limpiable_id'], 'idx_solicitud_limpieza_limpiable');
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
