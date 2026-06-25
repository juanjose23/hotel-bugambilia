<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DETALLE DE DEVOLUCIONES DE COMPRAS (Returned Items)
     *
     * Líneas de detalle de cada devolución al proveedor. Incluye referencia
     * al lote de inventario afectado, permitiendo que el sistema registre
     * automáticamente el movimiento de salida en inv_movimientos.
     */
    public function up(): void
    {
        Schema::create('devolucion_items', function (Blueprint $table) {
            $table->comment('Detalle de productos devueltos al proveedor en cada devolución de compra. Referencia al lote permite registrar la baja en inv_movimientos.');
            $table->id()->comment('Identificador único autoincremental del ítem de la devolución');
            $table->foreignId('devolucion_id')
                ->comment('Devolución a la que pertenece este ítem (FK → devoluciones_compra, cascadeOnDelete)')
                ->constrained('devoluciones_compra')
                ->cascadeOnDelete();

            $table->foreignId('lote_id')
                ->nullable()
                ->comment('Lote de inventario que se devuelve al proveedor. NULL si no se conoce el lote exacto. FK → inv_lotes, nullOnDelete.')
                ->constrained('inv_lotes')
                ->nullOnDelete();

            $table->foreignId('recepcion_item_id')
                ->nullable()
                ->comment('Ítem de recepción original que se está devolviendo. Trazabilidad P2P completa. FK → recepcion_items, nullOnDelete.')
                ->constrained('recepcion_items')
                ->nullOnDelete();

            $table->foreignId('producto_id')
                ->comment('Producto físico devuelto (FK → productos, cascadeOnDelete)')
                ->constrained('productos');

            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Variante específica del producto devuelto. FK → producto_variantes, nullOnDelete.')
                ->constrained('producto_variantes')
                ->nullOnDelete();

            $table->foreignId('unidad_medida_id')
                ->nullable()
                ->comment('Unidad de medida en que se devuelve el producto. FK → catalogos, nullOnDelete.')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->decimal('cantidad_devolver', 12, 2)->comment('Cantidad física que se regresa al proveedor');

            $table->timestamps();
            $table->softDeletes();

            $table->index('lote_id');
            $table->index('producto_id');
            $table->index('devolucion_id');
            $table->index('recepcion_item_id');
            $table->index('producto_variante_id');
            $table->index('unidad_medida_id');
        });
    }

    public function down(): void
    {
        Schema::table('devolucion_items', function (Blueprint $t) {
            $t->dropIndex(['devolucion_id']);
            $t->dropIndex(['recepcion_item_id']);
            $t->dropIndex(['producto_variante_id']);
            $t->dropIndex(['unidad_medida_id']);
        });
        Schema::dropIfExists('devolucion_items');
    }
};
