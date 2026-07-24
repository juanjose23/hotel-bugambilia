<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesos_cocina', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique()->comment('Código único de orden de producción/proceso en cocina');
            $table->foreignId('plato_id')->nullable()->comment('FK al plato preparado si proviene de orden de restaurante')->constrained('platos')->nullOnDelete();
            $table->unsignedSmallInteger('cantidad_platos')->nullable()->comment('Número de platos producidos a partir de la receta');
            $table->foreignId('producto_origen_id')->comment('FK al insumo o materia prima origen consumida')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('variante_origen_id')->nullable()->comment('FK a variante origen si aplica')->constrained('producto_variantes')->nullOnDelete();
            $table->decimal('cantidad_procesada', 10, 3)->comment('Cantidad total procesada del insumo origen');
            $table->decimal('costo_total', 10, 2)->comment('Costo total acumulado de la orden de cocina');
            $table->foreignId('realizado_por')->nullable()->comment('FK al usuario/cocinero que ejecutó el proceso')->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable()->comment('Observaciones de producción o merma');
            $table->timestamps();
            $table->softDeletes();

            $table->index('plato_id');
            $table->index('producto_origen_id');
            $table->index('realizado_por');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos_cocina');
    }
};
