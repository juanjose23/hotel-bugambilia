<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     *  MÓDULO: POLÍTICAS
     *
     *  Tabla maestra de políticas reutilizables dentro del sistema.
     *  Las políticas representan reglas, condiciones o lineamientos
     *  que pueden aplicarse a múltiples entidades del sistema:
     *
     *  - Habitaciones
     *  - Espacios
     *  - Reservas
     *  - Tarifas
     *  - Servicios
     *  - Promociones
     *  - Etc.
     *
     *  Ejemplos:
     *  - No fumar
     *  - No mascotas
     *  - Cancelación flexible
     *  - Check-in desde las 2 PM
     *  - Política de daños
     */
    public function up(): void
    {
        //
        Schema::create('politicas', function (Blueprint $table) {
            $table->comment('Tabla para registrar politicas tanto en habitaciones y espacios o servicios.');
            $table->id()->comment('Identificador unico de politicas');
            $table->string('titulo')->comment('Titulo de la politica')->unique();
            $table->text('descripcion')->comment('Descripcion de la politica')->nullable();
            $table->integer('estado')->default(1)->comment('Estado de la politica 1= Activo, 0=Inactivo');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['estado'], 'fk_politica_estado1_idx');
            $table->index(['titulo'], 'fk_politica_titulo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::drop('politicas');
    }
};
