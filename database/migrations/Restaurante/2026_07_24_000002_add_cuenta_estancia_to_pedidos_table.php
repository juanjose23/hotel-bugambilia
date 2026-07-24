<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->foreignId('cuenta_estancia_id')->nullable()->constrained('cuentas_estancia')->nullOnDelete()->after('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropForeign(['cuenta_estancia_id']);
            $table->dropColumn('cuenta_estancia_id');
        });
    }
};
