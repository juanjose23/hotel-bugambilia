<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: ÓRDENES DE COMPRA (Purchase Orders)
     *
     * Formalización del contrato de suministro entre el hotel y el proveedor.
     * Vincula la demanda interna (Solicitud) con la oferta ganadora (Cotización).
     */
    public function up(): void
    {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id()->comment('Identificador único del contrato de compra');
            $table->string('codigo', 30)->unique()->comment('Folio oficial (OC-YYYY-NNN)');

            $table->foreignId('proveedor_id')
                ->comment('Contratista o proveedor adjudicado')
                ->constrained('proveedores')
                ->cascadeOnDelete();

            $table->foreignId('solicitud_id')
                ->nullable()
                ->comment('Referencia a la requisición original')
                ->constrained('solicitudes_compra')
                ->nullOnDelete();

            $table->foreignId('cotizacion_id')
                ->nullable()
                ->comment('Vínculo a la oferta comercial aceptada (Trazabilidad P2P)')
                ->constrained('cotizaciones')
                ->nullOnDelete();

            $table->date('fecha_orden')->comment('Fecha de formalización del pedido');
            $table->date('fecha_entrega_estimada')->nullable()->comment('Compromiso de entrega del proveedor');

            $table->foreignId('condicion_pago_id')
                ->nullable()
                ->comment('Términos de crédito y liquidación')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->decimal('subtotal', 15, 2)->default(0)->comment('Monto neto');
            $table->decimal('impuestos', 15, 2)->default(0)->comment('IVA y otras cargas');
            $table->decimal('total', 15, 2)->default(0)->comment('Valor total del compromiso financiero');

            $table->integer('estado')->default(1)->comment('Fase del ciclo de vida (Enum EstadoOrdenCompra)');
            $table->text('notas')->nullable()->comment('Términos especiales o instrucciones de entrega');

            $table->timestamps();
            $table->softDeletes();

            $table->index('proveedor_id');
            $table->index('solicitud_id');
            $table->index('cotizacion_id');
            $table->index('estado');
        });

        // Constraint de dominio para estados (1=Borrador, 2=Emitida, 3=En Tránsito, 4=Recibida, 5=Cancelada)
        DB::statement('ALTER TABLE ordenes_compra ADD CONSTRAINT chk_ordenes_estado CHECK (estado IN (1,2,3,4,5))');
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_compra');
    }
};
