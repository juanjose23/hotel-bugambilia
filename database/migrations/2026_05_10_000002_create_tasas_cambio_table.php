<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: TASA DE CAMBIO (Exchange Rates)
     *
     * Permite registrar la tasa de cambio diaria entre monedas (ej. USD -> NIO)
     * utilizando claves foráneas para integridad referencial total.
     */
    public function up(): void
    {
        Schema::create('tasas_cambio', function (Blueprint $table) {
            $table->comment('Tabla que registra el historial diario y vigencia de tasas de cambio cambiarias entre las diferentes monedas.');
            $table->id()->comment('Identificador único autoincremental de la tasa de cambio');
            $table->date('fecha')->comment('Fecha de la tasa de cambio');

            $table->foreignId('moneda_origen_id')
                ->comment('Moneda que sirve como base (ej. USD)')
                ->constrained('monedas')
                ->cascadeOnDelete();

            $table->foreignId('moneda_destino_id')
                ->comment('Moneda de conversión destino (ej. NIO)')
                ->constrained('monedas')
                ->cascadeOnDelete();

            $table->decimal('tasa', 10, 4)->comment('Valor de la tasa de cambio (ej. 36.5200)');
            $table->boolean('es_fija')->default(false)->comment('Define si es una tasa fija/estándar de respaldo');
            $table->timestamps();

            $table->unique(['fecha', 'moneda_origen_id', 'moneda_destino_id'], 'uq_tasa_fecha_monedas_ids');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasas_cambio');
    }
};
