<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: COTIZACIONES (Vendor Offers)
     *
     * Gestiona las ofertas recibidas de proveedores para una solicitud específica.
     * Implementa auditoría de quién creó y seleccionó la oferta ganadora.
     */
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->comment('Tabla que registra las cotizaciones y ofertas comerciales de precios presentadas por proveedores para una requisición.');
            $table->id()->comment('Identificador único autoincremental de la cotización');
            $table->foreignId('solicitud_id')
                ->comment('Solicitud que origina el proceso concursal')
                ->constrained('solicitudes_compra')
                ->cascadeOnDelete();

            $table->foreignId('proveedor_id')
                ->comment('Entidad que emite la cotización')
                ->constrained('proveedores')
                ->cascadeOnDelete();

            $table->date('fecha_cotizacion')->comment('Fecha de emisión por el proveedor');
            $table->date('fecha_vencimiento')->nullable()->comment('Fecha de validez de la oferta');
            $table->integer('dias_entrega')->default(0)->comment('Tiempo de entrega en días hábiles');

            $table->foreignId('condicion_pago_id')
                ->nullable()
                ->comment('Términos financieros propuestos')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->decimal('subtotal', 15, 2)->default(0)->comment('Monto antes de impuestos');
            $table->decimal('impuestos', 15, 2)->default(0)->comment('Carga impositiva');
            $table->decimal('costo_envio', 15, 2)->default(0)->comment('Logística y fletes');
            $table->decimal('descuento', 15, 2)->default(0)->comment('Reducciones comerciales');
            $table->decimal('total', 15, 2)->default(0)->comment('Monto final de la oferta');

            $table->foreignId('moneda_id')
                ->default(2)
                ->comment('Divisa pactada')
                ->constrained('monedas')
                ->cascadeOnDelete();
            $table->decimal('tasa_cambio', 10, 4)->default(1.0000)->comment('Tasa de cambio aplicada al momento de la cotización');
            $table->string('archivo_pdf')->nullable()->comment('Soporte documental adjunto');

            $table->boolean('es_elegida')->default(false)->comment('Marca la oferta como ganadora para generación de OC');
            $table->integer('estado')->default(0)->comment('0: Activa, 1: Aceptada, 2: Aceptada Parcial, 3: Rechazada');
            $table->text('observaciones')->nullable()->comment('Notas de negociación');

            // Auditoría y Trazabilidad
            $table->foreignId('creada_por')->nullable()->comment('Usuario que registró la oferta')->constrained('users')->nullOnDelete();
            $table->foreignId('elegida_por')->nullable()->comment('Usuario que autorizó la adjudicación')->constrained('users')->nullOnDelete();
            $table->timestamp('elegida_en')->nullable()->comment('Timestamp de la decisión de compra');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['solicitud_id', 'proveedor_id']);
            $table->index('condicion_pago_id');
            $table->index('moneda_id');
            $table->index('creada_por');
            $table->index('elegida_por');
            $table->index('estado');
        });

        // Garantizar que solo exista una cotización elegida por solicitud
        try {
            DB::statement('CREATE UNIQUE INDEX uq_cotizacion_elegida_por_solicitud ON cotizaciones (solicitud_id) WHERE es_elegida = true AND deleted_at IS NULL');
        } catch (Exception $e) {
        }
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $t) {
            $t->dropIndex(['condicion_pago_id']);
            $t->dropIndex(['moneda_id']);
            $t->dropIndex(['creada_por']);
            $t->dropIndex(['elegida_por']);
            $t->dropIndex(['estado']);
        });
        Schema::dropIfExists('cotizaciones');
    }
};
