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
            $table->foreignId('moneda_id')->nullable()->after('reserva_id')->constrained('monedas')->nullOnDelete();
        });

        Schema::table('pagos_cuenta', function (Blueprint $table): void {
            $table->foreignId('moneda_id')->nullable()->after('forma_pago')->constrained('monedas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos_cuenta', function (Blueprint $table): void {
            $table->dropForeign(['moneda_id']);
            $table->dropColumn('moneda_id');
        });

        Schema::table('cuentas', function (Blueprint $table): void {
            $table->dropForeign(['moneda_id']);
            $table->dropColumn('moneda_id');
        });
    }
};
