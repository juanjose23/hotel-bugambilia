<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->comment('FK al pedido/comanda padre')->constrained('pedidos')->restrictOnDelete();
            $table->foreignId('plato_id')->nullable()->comment('FK al platillo solicitado')->constrained('platos')->nullOnDelete();
            $table->string('area_cocina')->default('cocina')->comment('Área de cocina: cocina, bar, postres, parrilla');
            $table->decimal('cantidad', 10, 2)->default(1)->comment('Cantidad de raciones o unidades');
            $table->decimal('precio_unitario', 10, 2)->comment('Precio unitario pactado al momento de ordenar');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal calculado (cantidad * precio_unitario)');
            $table->unsignedSmallInteger('estado')->default(1)->comment('EstadoItemPedido: 1=Pendiente, 2=EnPreparacion, 3=Listo, 4=Servido, 5=Anulado');
            $table->text('notas')->nullable()->comment('Especificaciones de preparación');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales de cocina');
            $table->timestamps();

            $table->index('pedido_id');
            $table->index('plato_id');
            $table->index('estado');
            $table->index('area_cocina');
            $table->index(['pedido_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};
