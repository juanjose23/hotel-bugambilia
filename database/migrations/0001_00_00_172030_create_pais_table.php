<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla paises con códigos ISO estándar.
     * Catálogo base de países usado por personas y direcciones.
     */
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_iso2', 2)->unique()->comment('Código ISO 3166-1 alpha-2 (ej. MX, US)');
            $table->string('codigo_iso3', 3)->unique()->comment('Código ISO 3166-1 alpha-3 (ej. MEX, USA)');
            $table->string('nombre', 150)->unique()->comment('Nombre oficial del país en español');
            $table->string('codigo_telefono', 10)->nullable()->comment('Código telefónico internacional (ej. +52, +1)');
            $table->integer('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paises');
    }
};
