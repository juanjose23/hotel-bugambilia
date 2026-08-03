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
            $table->foreignId('moneda_id')->nullable()->after('promocion_id')->constrained('monedas')->nullOnDelete();
            $table->string('tipo_pago', 30)->default('sin_pago')->after('total');
            $table->decimal('total_pagado', 12, 2)->default(0)->after('tipo_pago');
            $table->decimal('saldo', 12, 2)->default(0)->after('total_pagado');
            $table->json('meta_datos')->nullable()->after('saldo');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('moneda_id');
            $table->dropColumn(['tipo_pago', 'total_pagado', 'saldo', 'meta_datos']);
        });
    }
};
