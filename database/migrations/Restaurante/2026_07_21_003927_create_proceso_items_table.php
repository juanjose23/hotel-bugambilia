<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->comment('FK al proceso de cocina padre')->constrained('procesos_cocina')->cascadeOnDelete();
            $table->foreignId('producto_destino_id')->comment('FK al producto/subproducto obtenido')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('variante_destino_id')->nullable()->comment('FK a la variante de producto obtenida')->constrained('producto_variantes')->nullOnDelete();
            $table->decimal('cantidad', 10, 3)->comment('Cantidad producida u obtenida');
            $table->decimal('peso_unitario', 8, 3)->nullable()->comment('Peso unitario de porcionado en kg');
            $table->decimal('peso_total', 8, 3)->nullable()->comment('Peso total en kg del lote procesado');
            $table->decimal('costo_asignado', 10, 2)->comment('Costo financiero asignado al item procesado');
            $table->boolean('es_merma')->default(false)->comment('Indica si este item representa mermas/desperdicios');
            $table->foreignId('ubicacion_destino_id')->nullable()->comment('FK al almacén/ubicación de destino del producto')->constrained('ubicaciones')->nullOnDelete();
            $table->timestamps();

            $table->index('proceso_id');
            $table->index('producto_destino_id');
            $table->index('ubicacion_destino_id');
            $table->index('es_merma');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_items');
    }
};
