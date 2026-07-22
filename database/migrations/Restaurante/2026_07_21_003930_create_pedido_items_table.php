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
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('plato_id')->nullable()->constrained('platos')->nullOnDelete();
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('estado', 20)->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index('pedido_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};
