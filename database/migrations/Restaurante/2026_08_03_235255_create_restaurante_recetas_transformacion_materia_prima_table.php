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
        Schema::create('restaurante_recetas_transformacion_materia_prima', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_materia_prima_id')->constrained('productos');
            $table->foreignId('variante_materia_prima_id')->constrained('producto_variantes');
            $table->foreignId('producto_bruto_id')->constrained('productos');
            $table->foreignId('variante_bruta_id')->constrained('producto_variantes');
            $table->decimal('cantidad_bruta', 12, 4);
            $table->decimal('cantidad_resultado', 12, 4);
            $table->decimal('merma_estimada', 12, 4)->default(0);
            $table->foreignId('unidad_medida_id')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->boolean('estado')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['variante_materia_prima_id', 'variante_bruta_id', 'deleted_at'], 'uq_receta_tmp_variantes');
            $table->index(['variante_materia_prima_id', 'estado'], 'idx_receta_tmp_materia_estado');
            $table->index('variante_bruta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurante_recetas_transformacion_materia_prima');
    }
};
