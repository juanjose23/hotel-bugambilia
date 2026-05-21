<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: DEVOLUCIONES DE COMPRAS (Purchase Returns)
     *
     * Registra la salida física de productos devueltos a los proveedores
     * por calidad deficiente, daños en tránsito o excedentes no aceptados.
     * Vinculado con Órdenes de Compra y Recepciones para auditoría completa.
     *
     * NOTA: devoluciones_compra depende de ordenes_compra y recepciones_compra
     * (módulo Compras). La tabla devolucion_items depende adicionalmente de
     * inv_lotes (módulo Inventario), por eso ambas tablas van DESPUÉS de
     * inv_lotes en el orden de migraciones.
     */
    public function up(): void
    {
        Schema::create('devoluciones_compra', function (Blueprint $table) {
            $table->comment('Devoluciones de mercancía al proveedor por calidad, daños o excedentes. Vinculada a OC y Recepción para trazabilidad P2P completa.');
            $table->id()->comment('Identificador único autoincremental de la devolución de compra');
            $table->string('codigo')->unique()->comment('Código único de la devolución (DEV-YYYY-NNN)');

            $table->foreignId('orden_compra_id')
                ->comment('Orden de compra de origen de la devolución (FK → ordenes_compra, cascadeOnDelete)')
                ->constrained('ordenes_compra')
                ->cascadeOnDelete();

            $table->foreignId('recepcion_compra_id')
                ->nullable()
                ->comment('Recepción de compra donde se recibió la mercancía que ahora se devuelve. FK → recepciones_compra, nullOnDelete.')
                ->constrained('recepciones_compra')
                ->nullOnDelete();

            $table->date('fecha_devolucion')->comment('Fecha efectiva de salida física de la mercancía del hotel');
            $table->integer('estado')->default(0)->comment('Estado de la devolución (Enum EstadoDevolucion)');
            $table->text('motivo')->comment('Justificación técnica u operativa de la devolución (requerido)');
            $table->string('documento_externo', 100)->nullable()->comment('Guía de despacho de salida o Nota de Crédito del proveedor vinculada');

            $table->foreignId('creado_por_id')
                ->comment('Usuario responsable de emitir la devolución. FK → users, cascadeOnDelete.')
                ->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->index('orden_compra_id');
            $table->index('recepcion_compra_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoluciones_compra');
    }
};
