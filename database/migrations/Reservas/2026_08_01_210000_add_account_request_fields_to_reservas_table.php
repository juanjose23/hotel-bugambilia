<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->boolean('solicita_cuenta')
                ->default(false)
                ->after('ninos')
                ->comment('Solicita apertura de cuenta de consumo');
            $table->decimal('limite_cuenta_solicitado', 12, 2)
                ->nullable()
                ->after('solicita_cuenta')
                ->comment('Límite de crédito solicitado para la cuenta');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropColumn([
                'solicita_cuenta',
                'limite_cuenta_solicitado',
            ]);
        });
    }
};
