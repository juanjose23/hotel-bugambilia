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
        Schema::create('recursos_reservables', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tipo')->comment('1=habitación, 2=espacio, 3=servicio, 4=paquete');
            $table->string('nombre', 150);
            $table->unsignedSmallInteger('capacidad')->nullable();
            $table->unsignedTinyInteger('control_disponibilidad')
                ->comment('1=fechas, 2=horario, 3=cupos, 4=sin bloqueo');
            $table->unsignedInteger('duracion_minutos')->nullable();
            $table->unsignedTinyInteger('estado')->default(1)
                ->comment('1=activo, 2=inactivo, 3=mantenimiento');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo', 'estado']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos_reservables');
    }
};
