<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla catalogo_tipos (clasificación de catálogos).
     * Define las categorías de catálogos disponibles en el sistema.
     * Ejemplos: cargos, departamentos, categorías de producto, marcas,
     * unidades de medida, tipos de cliente, etc.
     */
    public function up(): void
    {
        Schema::create('catalogo_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código identificador único del tipo de catálogo (ej. CARGOS, DEPTOS, CAT_PROD)');
            $table->string('nombre', 150)->comment('Nombre descriptivo del tipo de catálogo');
            $table->integer('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_tipos');
    }
};
