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
        Schema::create('detalles_habitacion', function (Blueprint $table) {
            $table->comment('Tabla que almacena los detalles específicos de cada habitación');

            $table->id()->comment('Identificador único del detalle');
            $table->foreignId('habitacion_id')
                ->unique()
                ->comment('Identificador de la habitación (relación 1:1)')
                ->constrained('habitaciones')
                ->cascadeOnDelete();

            $table->integer('capacidad_adultos')
                ->default(2)
                ->comment('Capacidad máxima de adultos');

            $table->integer('capacidad_ninos')
                ->default(0)
                ->comment('Capacidad máxima de niños');

            $table->decimal('medidas', 7, 2)
                ->nullable()
                ->comment('Metros cuadrados de la habitación');

            $table->json('vistas')
                ->nullable()
                ->comment('Array de IDs de catálogo de tipo TIPO_VISTA (vistas disponibles)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_habitacion');
    }
};
