<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DETALLE DE RECEPCIÓN (Received Items)
     *
     * Registra cada producto, variante y cantidad recibidos físicamente
     * contra la orden de compra. Soporta lote de proveedor y fecha de vencimiento
     * para alimentar directamente al módulo de inventario (RegistrarEntradaRecepcion).
     */
    public function up(): void
    {
        Schema::create('recepcion_items', function (Blueprint $table) {
            $table->comment('Tabla de detalle que registra cada producto, variante y cantidad recibidos físicamente contra una orden de compra.');
            $table->id()->comment('Identificador único autoincremental del ítem recibido');
            $table->foreignId('recepcion_id')->constrained('recepciones_compra')->cascadeOnDelete();

            $table->foreignId('orden_item_id')
                ->comment('Referencia al ítem del contrato (OC)')
                ->constrained('orden_compra_items')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->comment('Producto físico recibido')
                ->constrained('productos');

            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Especificación técnica recibida')
                ->constrained('producto_variantes')
                ->nullOnDelete();

            $table->foreignId('unidad_medida_id')
                ->nullable()
                ->comment('Unidad de medida física registrada')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->decimal('cantidad_recibida', 12, 2)->comment('Cantidad neta aceptada');
            $table->decimal('cantidad_rechazada', 12, 2)->default(0)->comment('Cantidad devuelta al proveedor');

            $table->string('motivo_rechazo')->nullable()->comment('Causa de la no conformidad');
            $table->text('nota')->nullable()->comment('Observaciones generales del ítem');
            $table->string('lote_proveedor', 100)->nullable()->comment('Lote del fabricante/proveedor');
            $table->date('fecha_vencimiento')->nullable()->comment('Fecha de vencimiento/caducidad');

            $table->foreignId('ubicacion_id')
                ->nullable()
                ->comment('Ubicación física de destino específica para este ítem (sobreescribe la de la cabecera)')
                ->constrained('ubicaciones')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_items');
    }
};
