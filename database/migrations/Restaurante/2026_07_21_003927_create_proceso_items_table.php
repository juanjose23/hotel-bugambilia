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
            $table->foreignId('proceso_id')->constrained('procesos_cocina')->cascadeOnDelete();
            $table->foreignId('producto_destino_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('variante_destino_id')->nullable()->constrained('producto_variantes')->nullOnDelete();
            $table->decimal('cantidad', 10, 3);
            $table->decimal('peso_unitario', 8, 3)->nullable();
            $table->decimal('peso_total', 8, 3)->nullable();
            $table->decimal('costo_asignado', 10, 2);
            $table->boolean('es_merma')->default(false);
            $table->foreignId('ubicacion_destino_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->timestamps();
            $table->index('proceso_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_items');
    }
};
