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
        Schema::create('inv_prefijos_codigo', function (Blueprint $table) {
            $table->comment('Generador de códigos correlativos únicos para activos sin colisión concurrente');
            $table->id()->comment('Identificador único autoincremental');
            $table->string('prefijo', 20)->unique()->comment('Prefijo de activo (ej. TV, AC, CAM)');
            $table->integer('ultimo_numero')->default(0)->comment('Último número secuencial asignado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_prefijos_codigo');
    }
};
