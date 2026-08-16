<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago_transacciones', function (Blueprint $table): void {
            $table->foreignId('reserva_id')
                ->nullable()
                ->after('pasarela_pago_id')
                ->constrained('reservas')
                ->nullOnDelete();

            $table->index(['reserva_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('pago_transacciones', function (Blueprint $table): void {
            $table->dropIndex(['reserva_id', 'estado']);
            $table->dropConstrainedForeignId('reserva_id');
        });
    }
};
