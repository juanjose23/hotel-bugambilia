<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicas', function (Blueprint $table) {
            $table->comment('Politicas reutilizables para habitaciones, espacios y servicios.');
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

    public function down(): void
    {
        Schema::dropIfExists('politicas');
    }
};
