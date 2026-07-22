<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DETALLE DE SOLICITUD (Line Items)
     *
     * Registra los productos, variantes y cantidades requeridas en cada solicitud.
     */
    public function up(): void
    {
        Schema::create('solicitud_items', function (Blueprint $table) {
            $table->comment('Tabla de detalle que registra cada uno de los productos y variantes solicitados en una requisición de compra.');
            $table->id()->comment('Identificador único autoincremental de la línea de detalle de la solicitud de compra');
            $table->foreignId('solicitud_id')->constrained('solicitudes_compra')->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->comment('Vínculo al catálogo de productos')
                ->constrained('productos');

            $table->foreignId('producto_variante_id')
                ->nullable()
                ->comment('Especificación técnica (Variante)')
                ->constrained('producto_variantes')
                ->nullOnDelete();

            $table->foreignId('unidad_medida_id')
                ->nullable()
                ->comment('Magnitud de medida pactada')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->decimal('cantidad_solicitada', 12, 2)->comment('Cantidad original solicitada');
            $table->decimal('cantidad_aprobada', 12, 2)->default(0)->comment('Cantidad validada por compras');

            $table->text('observaciones')->nullable()->comment('Especificaciones del ítem');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['solicitud_id', 'producto_id']);
            $table->index('producto_variante_id');
            $table->index('unidad_medida_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_items', function (Blueprint $t) {
            $t->dropIndex(['producto_variante_id']);
            $t->dropIndex(['unidad_medida_id']);
        });
        Schema::dropIfExists('solicitud_items');
    }
};
