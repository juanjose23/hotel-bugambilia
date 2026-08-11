<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código correlativo único del platillo en restaurante');
            $table->string('nombre', 100)->comment('Nombre comercial del plato mostrado en menú');
            $table->foreignId('categoria_id')->nullable()->comment('FK a categoría de menú (entradas, fuertes, postres, bebidas)')->constrained('catalogos')->nullOnDelete();
            $table->foreignId('producto_receta_id')->nullable()->comment('FK al producto de tipo receta para consumo de insumos')->constrained('productos')->nullOnDelete();
            $table->text('descripcion')->nullable()->comment('Detalle de ingredientes y notas comerciales del plato');
            $table->boolean('web')->default(false)->comment('Indica si el plato es visible en la carta web/QR');
            $table->integer('estado')->default(1)->comment('Estado del plato: 1=Activo/Disponible, 0=Inactivo/Agotado');
            $table->string('tiempo_preparacion', 50)->nullable()->comment('Tiempo estimado de preparación (ej. 15-20 min)');
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('categoria_id');
            $table->index('producto_receta_id');
            $table->index('web');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platos');
    }
};
