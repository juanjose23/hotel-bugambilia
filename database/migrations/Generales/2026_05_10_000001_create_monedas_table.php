<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: MONEDAS (Currencies)
     *
     * Permite gestionar las divisas utilizadas en compras e inventario,
     * identificando cuál es la moneda por defecto del hotel (Córdoba Nicaragüense).
     */
    public function up(): void
    {
        Schema::create('monedas', function (Blueprint $table) {
            $table->comment('Tabla paramétrica que registra las divisas o monedas aceptadas y manejadas por el hotel.');
            $table->id()->comment('Identificador único autoincremental de la moneda');
            $table->string('codigo', 3)->unique()->comment('Código ISO de la divisa (ej. USD, NIO)');
            $table->string('nombre', 100)->comment('Nombre completo de la moneda (ej. Córdoba Nicaragüense)');
            $table->string('simbolo', 10)->comment('Símbolo monetario (ej. C$, $)');
            $table->boolean('es_predeterminada')->default(false)->comment('Identifica si es la moneda base del hotel');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monedas');
    }
};
