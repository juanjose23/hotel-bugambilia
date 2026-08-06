<?php

declare(strict_types=1);

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
        Schema::table('pedido_items', function (Blueprint $table) {
            $table->json('bloqueo_stock_detalle')
                ->nullable()
                ->after('estado')
                ->comment('Detalle de ingredientes faltantes que bloquean el envío del item a cocina.');
            $table->timestamp('bloqueado_stock_en')
                ->nullable()
                ->after('bloqueo_stock_detalle')
                ->index()
                ->comment('Fecha en que el item quedó bloqueado por falta de stock.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_items', function (Blueprint $table) {
            $table->dropColumn(['bloqueo_stock_detalle', 'bloqueado_stock_en']);
        });
    }
};
