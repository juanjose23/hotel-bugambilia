<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: LOTES DE INVENTARIO (inv_lotes)
     *
     * Corazón operativo del control de inventario de consumibles.
     * Registra cada lote de mercancía recibida, su cantidad disponible actual
     * y su fecha de vencimiento para la estrategia FEFO (First-Expiry-First-Out).
     *
     * GENERACIÓN DEL codigo_lote:
     *   - Si el proveedor envió su propio número: se usa tal cual (lote_proveedor).
     *   - Si no: sprintf('LOTE-%d-%s', $productoId, now()->format('Ymd'))
     *     Ejemplo: LOTE-15-20260520
     *   - En recepciones ConDiscrepancia, se añaden sufijos:
     *     LOTE-15-20260520-DISP (disponible) / LOTE-15-20260520-CUAR (cuarentena)
     */
    public function up(): void
    {
        Schema::create('inv_lotes', function (Blueprint $table) {
            $table->comment('Lotes de inventario de consumibles. Corazón del control de stock. Cada lote tiene cantidad disponible, vencimiento y estado para la estrategia FEFO.');
            $table->id()->comment('Identificador único autoincremental del lote de inventario');
            $table->string('codigo_lote', 100)->comment('Código único del lote. Formato: LOTE-{productoId}-{Ymd} o tomado del proveedor. Ver RegistrarEntradaRecepcion.');
            $table->foreignId('producto_id')
                ->comment('Producto al que pertenece este lote (FK → productos, cascadeOnDelete)')
                ->constrained('productos');
            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Variante específica del producto. NULL si aplica a todas las variantes. FK → producto_variantes, nullOnDelete.')
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->tinyInteger('estado')
                ->default(1)
                ->comment('Estado del lote (Enum EstadoLote): 1=Disponible, 2=Cuarentena, 0=Agotado, 3=Vencido, 4=Rechazado');
            $table->decimal('cantidad_disponible', 14, 4)
                ->default(0)
                ->comment('Cantidad actual disponible para consumos. Se decrementa con cada ConsumirStock o TrasladarEntreBodegas.');
            $table->decimal('cantidad_inicial', 14, 4)
                ->default(0)
                ->comment('Cantidad original al momento de la recepción. Nunca cambia después de la creación del lote.');
            $table->decimal('costo_unitario', 14, 6)->nullable()->after('cantidad_inicial')->comment('Costo unitario del producto en este lote');
            $table->decimal('costo_total', 14, 2)->nullable()->after('costo_unitario')->comment('Costo total del lote (cantidad_inicial * costo_unitario)');
            $table->foreignId('ubicacion_id')
                ->nullable()
                ->comment('Almacén o bodega inicial donde fue ubicado el lote. Asignado por PutawayPolicy si no se especifica. FK → ubicaciones, nullOnDelete.')
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->foreignId('ubicacion_detalle_id')
                ->nullable()
                ->comment('Sub-ubicación específica dentro del almacén (estante/nivel/posición). NULL si no está detallada. FK → ubicaciones, nullOnDelete.')
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->date('fecha_vencimiento')
                ->nullable()
                ->comment('Fecha de caducidad. NULL para productos sin vencimiento. ESENCIAL para el algoritmo FEFO. Nulos van al final de la cola.');
            $table->string('lote_proveedor', 100)
                ->nullable()
                ->comment('Número de lote original del fabricante o proveedor. Permite trazabilidad inversa hacia el proveedor.');
            $table->foreignId('proveedor_id')
                ->nullable()
                ->comment('Proveedor del que se recibió este lote. FK → proveedores, nullOnDelete.')
                ->constrained('proveedores')
                ->nullOnDelete();
            $table->date('fecha_recepcion')
                ->comment('Fecha en que físicamente ingresó el lote al almacén del hotel');
            $table->foreignId('recepcion_item_id')
                ->nullable()
                ->comment('Ítem de recepción de compra que originó este lote. Trazabilidad P2P completa. FK → recepcion_items, nullOnDelete.')
                ->constrained('recepcion_items')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->comment('Borrado lógico. Los lotes eliminados mantienen historial en inv_movimientos.');

            $table->index(['producto_id', 'estado'], 'inv_lotes_producto_estado_index');
            $table->index(['estado', 'fecha_vencimiento'], 'inv_lotes_estado_vencimiento_index');
            $table->index('codigo_lote', 'uq_inv_lotes_codigo_lote');
            $table->index('producto_variante_id');
            $table->index('ubicacion_id');
            $table->index('proveedor_id');
            $table->index('recepcion_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('inv_lotes', function (Blueprint $t) {
            $t->dropIndex('uq_inv_lotes_codigo_lote');
            $t->dropIndex(['producto_variante_id']);
            $t->dropIndex(['ubicacion_id']);
            $t->dropIndex(['proveedor_id']);
            $t->dropIndex(['recepcion_item_id']);
            $t->dropColumn(['costo_unitario', 'costo_total']);
        });
        Schema::dropIfExists('inv_lotes');
    }
};
