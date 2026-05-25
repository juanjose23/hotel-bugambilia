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
        //
        /**
         * MÓDULO: ASIGNACIÓN POLIMÓRFICA DE POLÍTICAS
         *
         * Relaciona políticas con cualquier entidad del sistema
         * mediante una relación polimórfica.
         *
         * Permite reutilizar una misma política sobre:
         *
         * - Habitaciones
         * - Espacios
         * - Reservas
         * - Tarifas
         * - Servicios
         * - Promociones
         * - Otros módulos futuros
         *
         * Ejemplo:
         *
         * politica_id = 1
         * politicaable_type = habitacion
         * politicaable_id = 5
         */
        Schema::create('politicaables', function (Blueprint $table) {
            $table->comment('Relación polimórfica entre políticas y entidades del sistema');
            $table->id();
            $table->foreignId('politica_id')
                ->comment('Política asociada')
                ->constrained('politicas')
                ->cascadeOnDelete();
            $table->morphs('politicaable');
            $table->timestamps();
            $table->softDeletes();
            $table->unique([
                'politica_id',
                'politicaable_type',
                'politicaable_id',
            ], 'politicaables_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::drop('politicaables');
    }
};
