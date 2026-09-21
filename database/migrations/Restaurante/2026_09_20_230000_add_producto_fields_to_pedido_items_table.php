<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_items', function (Blueprint $table): void {
            $table->string('tipo_item', 20)->default('plato')->after('plato_id')->comment('Tipo de ítem: plato o producto');
            $table->foreignId('producto_id')->nullable()->after('tipo_item')->comment('FK a producto de inventario (si aplica)')->constrained('productos')->nullOnDelete();
            $table->foreignId('producto_variante_id')->nullable()->after('producto_id')->comment('FK a variante del producto (si aplica)')->constrained('producto_variantes')->nullOnDelete();

            $table->index('tipo_item');
            $table->index('producto_id');
            $table->index('producto_variante_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_items', function (Blueprint $table): void {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['producto_variante_id']);
            $table->dropIndex(['tipo_item']);
            $table->dropIndex(['producto_id']);
            $table->dropIndex(['producto_variante_id']);
            $table->dropColumn(['tipo_item', 'producto_id', 'producto_variante_id']);
        });
    }
};
