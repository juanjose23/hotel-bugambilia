<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserva_detalles', function (Blueprint $table): void {
            $table->dateTime('hold_expires_at')->nullable()->after('notas')->comment('Fecha y hora de expiración del apartado (hold)');
            $table->dateTime('confirmado_at')->nullable()->after('hold_expires_at')->comment('Fecha y hora de confirmación del detalle');
            $table->dateTime('cancelado_at')->nullable()->after('confirmado_at')->comment('Fecha y hora de cancelación del detalle');

            $table->index(['reservable_id', 'estado', 'fecha_inicio', 'fecha_fin'], 'res_det_recurso_est_periodo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reserva_detalles', function (Blueprint $table): void {
            $table->dropIndex('res_det_recurso_est_periodo_idx');
            $table->dropColumn(['hold_expires_at', 'confirmado_at', 'cancelado_at']);
        });
    }
};
