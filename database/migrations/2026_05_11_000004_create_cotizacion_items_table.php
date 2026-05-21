<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DETALLE DE COTIZACIONES (Quoted Items)
     *
     * Especifica cada producto, variante, cantidad y precio unitario ofertado
     * en una cotización de proveedor.
     */
    public function up(): void
    {
        Schema::create('cotizacion_items', function (Blueprint $table) {
            $table->comment('Tabla de detalle que registra cada producto, variante, cantidad y precio unitario ofertado en una cotización.');
            $table->id()->comment('Identificador único autoincremental del ítem de la cotización');
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('producto_variante_id')->nullable()->constrained('producto_variantes')->nullOnDelete();

            $table->decimal('cantidad', 12, 2)->comment('Cantidad ofertada');
            $table->decimal('precio_unitario', 12, 2)->comment('Valor por unidad');
            $table->decimal('subtotal', 12, 2)->comment('Cálculo: Cantidad * Precio');
            $table->boolean('es_elegido')->default(false)->comment('Marca el ítem individual como seleccionado');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_items');
    }
};
