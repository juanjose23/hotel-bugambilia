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
        Schema::create('reserva_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('reservable_id')->constrained('recursos_reservables')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('reserva_detalles')->nullOnDelete();
            $table->unsignedTinyInteger('estado')->default(1)
                ->comment('1=pendiente, 2=confirmado, 3=en uso, 4=completado, 5=cancelado, 6=reprogramado');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->unsignedSmallInteger('adultos')->default(0);
            $table->unsignedSmallInteger('ninos')->default(0);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reserva_id', 'estado']);
            $table->index(['reservable_id', 'estado']);
            $table->index(['reservable_id', 'fecha_inicio', 'fecha_fin'], 'res_det_recurso_periodo_idx');
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_detalles');
    }
};
