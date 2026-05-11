<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla productos del inventario del hotel.
     * Cada producto pertenece a una categoría y opcionalmente a
     * una marca y unidad de medida (valores de catálogos).
     * El campo tipo clasifica entre percedero (1) y no percedero (2).
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->comment('Categoría del producto (catálogo)')->constrained('catalogos')->cascadeOnDelete();
            $table->foreignId('marca_id')->nullable()->comment('Marca del producto (catálogo, opcional)')->constrained('catalogos')->cascadeOnDelete();
            $table->string('nombre', 200)->comment('Nombre del producto');
            $table->text('descripcion')->nullable()->comment('Descripción detallada del producto');
            $table->foreignId('unidad_medida_id')->nullable()->comment('Unidad de medida base (kg, lt, unidad, etc.)')->constrained('catalogos')->cascadeOnDelete();
            $table->integer('tipo')->nullable()->comment('1: Perecedero, 2: No perecedero');
            $table->integer('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->unique(['nombre', 'categoria_id'], 'uq_productos_nombre_categoria');
            $table->timestamps();
            $table->softDeletes();
            $table->index('categoria_id');
            $table->index('marca_id');
            $table->index('unidad_medida_id');
            $table->index('nombre');
        });
        DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_estado_productos CHECK (estado IN (0,1))');
        DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_tipo_productos CHECK (tipo IN (1,2))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
