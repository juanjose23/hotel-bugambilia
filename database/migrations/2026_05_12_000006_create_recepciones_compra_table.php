<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: RECEPCIÓN DE MERCANCÍA (Goods Arrival)
     *
     * Registra el ingreso físico de productos al almacén y valida contra el contrato (OC).
     * Implementa control de discrepancias y trazabilidad de quien recibe.
     */
    public function up(): void
    {
        Schema::create('recepciones_compra', function (Blueprint $table) {
            $table->id()->comment('ID de la fe de hechos de recepción');
            $table->string('codigo')->unique()->comment('Código de recepción (RC-YYYY-NNN)');

            $table->foreignId('orden_compra_id')
                ->comment('Orden de compra vinculada (Contrato origen)')
                ->constrained('ordenes_compra')
                ->cascadeOnDelete();

            $table->date('fecha_recepcion')->comment('Fecha de llegada física');
            $table->string('guia_remision', 50)->nullable()->comment('Documento de transporte del proveedor');

            $table->foreignId('recibido_por_id')
                ->comment('Personal responsable del conteo y verificación')
                ->constrained('users');

            $table->integer('estado')->default(1)->comment('Situación de la carga (Enum EstadoRecepcion)');
            $table->text('notas')->nullable()->comment('Observaciones sobre daños, faltantes o calidad');

            $table->timestamps();
            $table->softDeletes();

            $table->index('orden_compra_id');
            $table->index('recibido_por_id');
            $table->index('estado');
        });

        // Constraint de dominio (1=Completa, 2=Parcial, 3=Con Discrepancia, 4=Rechazada)
        DB::statement('ALTER TABLE recepciones_compra ADD CONSTRAINT chk_recepciones_estado CHECK (estado IN (1,2,3,4,5))');

        // Detalle de ítems recibidos
        Schema::create('recepcion_items', function (Blueprint $table) {
            $table->id();
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

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_items');
        Schema::dropIfExists('recepciones_compra');
    }
};
