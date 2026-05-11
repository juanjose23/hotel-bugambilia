<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla producto_variantes para las variantes de productos.
     * Un producto puede tener múltiples variantes (tallas, colores,
     * presentaciones) cada una con su propio código SKU, atributos,
     * peso y volumen. El código SKU es único a nivel global.
     */
    public function up(): void
    {
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->comment('FK al producto padre')->constrained('productos')->cascadeOnDelete();
            $table->string('codigo', 50)->unique()->comment('Código SKU único de la variante');
            $table->string('nombre_variante', 200)->comment('Nombre de la variante (ej. 500ml, Rojo, Premium)');
            $table->json('atributos')->nullable()->comment('Atributos específicos en JSON (color, talla, sabor, etc.)');
            $table->foreignId('unidad_medida_id')->nullable()->comment('Unidad de medida específica de la variante')->constrained('catalogos')->cascadeOnDelete();
            $table->decimal('peso', 8, 2)->nullable()->comment('Peso en gramos (opcional)');
            $table->decimal('volumen')->nullable()->comment('Volumen en mililitros (opcional)');
            $table->integer('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes();
            $table->index('producto_id');
            $table->index('unidad_medida_id');
            $table->index('nombre_variante');
            $table->unique(['producto_id', 'nombre_variante'], 'uq_variante_por_producto');
        });
        DB::statement('ALTER TABLE producto_variantes ADD CONSTRAINT chk_estado_producto_variantes CHECK (estado IN (0,1))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_variantes');
    }
};
