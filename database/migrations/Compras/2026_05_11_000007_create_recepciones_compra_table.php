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
     * Incluye columna ubicacion_id para definir el destino general de bodega de la recepción.
     */
    public function up(): void
    {
        Schema::create('recepciones_compra', function (Blueprint $table) {
            $table->comment('Tabla maestro que registra las recepciones físicas de mercancía o servicios recibidos del proveedor vinculadas a una orden de compra.');
            $table->id()->comment('Identificador único autoincremental de la recepción de compra');
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

            $table->foreignId('ubicacion_id')
                ->nullable()
                ->comment('Ubicación física de destino general para la recepción. Los ítems sin ubicación específica heredan esta.')
                ->constrained('ubicaciones')
                ->nullOnDelete();

            $table->integer('estado')->default(0)->comment('Situación de la carga (Enum EstadoRecepcion)');
            $table->text('notas')->nullable()->comment('Observaciones sobre daños, faltantes o calidad');

            $table->timestamps();
            $table->softDeletes();

            $table->index('orden_compra_id');
            $table->index('recibido_por_id');
            $table->index('estado');
        });

        // Constraint de dominio (0=Pendiente, 1=Completa, 2=Parcial, 3=Con Discrepancia, 4=Rechazada, 5=Devuelta/Cancelada)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE recepciones_compra ADD CONSTRAINT chk_recepciones_estado CHECK (estado IN (0,1,2,3,4,5))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones_compra');
    }
};
