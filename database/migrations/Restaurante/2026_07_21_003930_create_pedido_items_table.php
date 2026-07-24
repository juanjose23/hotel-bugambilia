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
            $table->foreignId('pedido_id')->comment('FK al pedido/comanda padre')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('plato_id')->nullable()->comment('FK al platillo solicitado')->constrained('platos')->nullOnDelete();
            $table->decimal('cantidad', 10, 2)->default(1)->comment('Cantidad de raciones o unidades');
            $table->decimal('precio_unitario', 10, 2)->comment('Precio unitario pactado al momento de ordenar');
            $table->decimal('subtotal', 10, 2)->comment('Subtotal calculado (cantidad * precio_unitario)');
            $table->string('estado', 20)->default('pendiente')->comment('Estado de preparación del ítem: pendiente, en_preparacion, listo, servido, anulado');
            $table->text('notas')->nullable()->comment('Especificaciones de preparación (ej. sin cebolla, término medio)');
            $table->timestamps();

            $table->index('pedido_id');
            $table->index('plato_id');
            $table->index('estado');
            $table->index(['pedido_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};
