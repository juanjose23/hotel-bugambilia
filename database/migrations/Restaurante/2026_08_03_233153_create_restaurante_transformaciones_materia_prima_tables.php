<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurante_transformaciones_materia_prima', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->foreignId('producto_origen_id')->constrained('productos');
            $table->foreignId('variante_origen_id')->constrained('producto_variantes');
            $table->foreignId('ubicacion_origen_id')->constrained('ubicaciones');
            $table->decimal('cantidad_procesada', 12, 4);
            $table->decimal('costo_total', 14, 2)->default(0);
            $table->foreignId('realizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['producto_origen_id', 'variante_origen_id']);
            $table->index('ubicacion_origen_id');
            $table->index('realizado_por');
        });

        Schema::create('restaurante_transformacion_materia_prima_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transformacion_id')
                ->constrained('restaurante_transformaciones_materia_prima')
                ->cascadeOnDelete();
            $table->foreignId('producto_destino_id')->constrained('productos');
            $table->foreignId('variante_destino_id')->nullable()->constrained('producto_variantes')->nullOnDelete();
            $table->foreignId('ubicacion_destino_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->decimal('cantidad', 12, 4);
            $table->decimal('costo_asignado', 14, 2)->default(0);
            $table->boolean('es_merma')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('transformacion_id');
            $table->index(['producto_destino_id', 'variante_destino_id']);
            $table->index('ubicacion_destino_id');
            $table->index('es_merma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurante_transformacion_materia_prima_items');
        Schema::dropIfExists('restaurante_transformaciones_materia_prima');
    }
};
