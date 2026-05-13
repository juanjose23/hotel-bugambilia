<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DETALLE DE ORDEN DE COMPRA (Contract Items)
     *
     * Especifica los bienes y servicios contratados, sus costos y especificaciones técnicas.
     */
    public function up(): void
    {
        Schema::create('orden_compra_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')
                ->comment('Contrato padre')
                ->constrained('ordenes_compra')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->comment('Ítem de inventario')
                ->constrained('productos');

            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Especificación técnica / Variante')
                ->constrained('producto_variantes')
                ->nullOnDelete();

            $table->foreignId('unidad_medida_id')
                ->nullable()
                ->comment('Unidad de medida facturada')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->decimal('cantidad', 12, 2)->comment('Cantidad pactada con el proveedor');
            $table->decimal('precio_unitario', 12, 2)->comment('Costo por unidad según cotización');
            $table->decimal('subtotal', 12, 2)->comment('Valor de la línea (Cant * Precio)');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['orden_compra_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compra_items');
    }
};
