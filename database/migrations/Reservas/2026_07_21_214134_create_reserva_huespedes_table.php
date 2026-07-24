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
        Schema::create('reserva_huespedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_detalle_id')->constrained('reserva_detalles')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->unsignedTinyInteger('tipo_identificacion')->nullable();
            $table->string('identificacion', 100)->nullable();
            $table->unsignedTinyInteger('tipo_huesped')->default(1)
                ->comment('1=adulto, 2=niño, 3=infante');
            $table->boolean('es_titular')->default(false);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->timestamps();

            $table->index(['reserva_detalle_id', 'tipo_huesped'], 'res_huesped_detalle_tipo_idx');
            $table->index('identificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_huespedes');
    }
};
