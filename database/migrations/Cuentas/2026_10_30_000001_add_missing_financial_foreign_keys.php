<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentas', function (Blueprint $table): void {
            $table->index('estancia_id');
            $table->foreign('estancia_id')->references('id')->on('estancias')->restrictOnDelete();
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->restrictOnDelete();
        });

        Schema::table('pagos_cuenta', function (Blueprint $table): void {
            $table->index('venta_id');
            $table->foreign('venta_id')->references('id')->on('ventas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos_cuenta', function (Blueprint $table): void {
            $table->dropForeign(['venta_id']);
            $table->dropIndex(['venta_id']);
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropForeign(['cuenta_id']);
        });

        Schema::table('cuentas', function (Blueprint $table): void {
            $table->dropForeign(['estancia_id']);
            $table->dropIndex(['estancia_id']);
        });
    }
};
