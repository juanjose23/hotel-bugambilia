<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurante_sustituciones_ingredientes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_item_id')->constrained('pedido_items')->cascadeOnDelete();
            $table->foreignId('plato_id')->nullable()->constrained('platos')->nullOnDelete();
            $table->foreignId('producto_original_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('variante_original_id')->constrained('producto_variantes')->restrictOnDelete();
            $table->foreignId('producto_sustituto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('variante_sustituta_id')->constrained('producto_variantes')->restrictOnDelete();
            $table->decimal('cantidad_requerida', 12, 4);
            $table->decimal('cantidad_usada', 12, 4);
            $table->string('motivo', 255)->nullable();
            $table->foreignId('autorizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('estado')->default(1)->comment('1=activa, 0=inactiva');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pedido_item_id', 'variante_original_id', 'estado'], 'rest_sust_item_original_estado_idx');
            $table->index(['variante_sustituta_id', 'estado'], 'rest_sust_sustituta_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurante_sustituciones_ingredientes');
    }
};
