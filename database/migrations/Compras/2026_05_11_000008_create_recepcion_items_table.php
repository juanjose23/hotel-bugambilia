<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            $table->index('recepcion_id');
            $table->index('orden_item_id');
            $table->index('producto_id');
            $table->index('producto_variante_id');
            $table->index('unidad_medida_id');
            $table->index('ubicacion_id');
        });

        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Función para validar cantidades en recepción
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION chk_cantidad_recepcion()
            RETURNS TRIGGER AS $$
            DECLARE
                cantidad_ordenada DECIMAL;
                total_recibido DECIMAL;
            BEGIN
                -- Obtener la cantidad total pactada en la Orden de Compra
                SELECT cantidad INTO cantidad_ordenada 
                FROM orden_compra_items 
                WHERE id = NEW.orden_item_id;

                -- Calcular lo ya recibido previamente (excluyendo el registro actual si es un update)
                SELECT COALESCE(SUM(cantidad_recibida), 0) INTO total_recibido 
                FROM recepcion_items 
                WHERE orden_item_id = NEW.orden_item_id 
                AND id != COALESCE(NEW.id, 0);

                -- Validar exceso
                IF (total_recibido + NEW.cantidad_recibida) > cantidad_ordenada THEN
                    RAISE EXCEPTION 'CONTROL INDUSTRIAL: La cantidad recibida (%) supera la cantidad ordenada (%) para el item ID: %', 
                        (total_recibido + NEW.cantidad_recibida), 
                        cantidad_ordenada, 
                        NEW.orden_item_id;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // Trigger vinculado a recepcion_items
        DB::statement('DROP TRIGGER IF EXISTS trg_chk_cantidad_recepcion ON recepcion_items');
        DB::statement('CREATE TRIGGER trg_chk_cantidad_recepcion BEFORE INSERT OR UPDATE ON recepcion_items FOR EACH ROW EXECUTE FUNCTION chk_cantidad_recepcion();');
    }

    public function down(): void
    {
        Schema::table('recepcion_items', function (Blueprint $t) {
            $t->dropIndex(['recepcion_id']);
            $t->dropIndex(['orden_item_id']);
            $t->dropIndex(['producto_id']);
            $t->dropIndex(['producto_variante_id']);
            $t->dropIndex(['unidad_medida_id']);
            $t->dropIndex(['ubicacion_id']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS trg_chk_cantidad_recepcion ON recepcion_items');
            DB::statement('DROP FUNCTION IF EXISTS chk_cantidad_recepcion()');
        }

        Schema::dropIfExists('recepcion_items');
    }
};
