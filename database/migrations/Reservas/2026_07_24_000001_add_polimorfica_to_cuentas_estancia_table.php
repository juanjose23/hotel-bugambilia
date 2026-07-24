<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentas_estancia', function (Blueprint $table): void {
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete()->after('estancia_id');
            $table->string('cuentaable_type')->nullable()->after('reserva_id');
            $table->unsignedBigInteger('cuentaable_id')->nullable()->after('cuentaable_type');
            $table->string('tipo_titular', 20)->default('HABITACION')->after('cuentaable_id');
            $table->string('numero_cuenta', 50)->nullable()->after('numero_folio');
            $table->decimal('monto_limite', 12, 2)->nullable()->after('limite_autorizado');

            $table->index(['cuentaable_type', 'cuentaable_id']);
        });

        Schema::table('cuentas_estancia', function (Blueprint $table): void {
            $table->dropForeign(['estancia_id']);
            $table->foreign('estancia_id')->references('id')->on('estancias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cuentas_estancia', function (Blueprint $table): void {
            $table->dropIndex(['cuentaable_type', 'cuentaable_id']);
            $table->dropColumn(['reserva_id', 'cuentaable_type', 'cuentaable_id', 'tipo_titular', 'numero_cuenta', 'monto_limite']);
        });

        Schema::table('cuentas_estancia', function (Blueprint $table): void {
            $table->dropForeign(['estancia_id']);
            $table->foreign('estancia_id')->references('id')->on('estancias')->cascadeOnDelete();
        });
    }
};
