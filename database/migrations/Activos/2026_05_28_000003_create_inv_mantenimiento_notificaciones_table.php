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
        Schema::create('inv_mantenimiento_notificaciones', function (Blueprint $table) {
            $table->comment('Historial e información de trazabilidad de notificaciones de mantenimiento enviadas');
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignId('mantenimiento_id')
                ->comment('Referencia al mantenimiento asociado')
                ->constrained('inv_mantenimientos')
                ->cascadeOnDelete();
            $table->string('tipo', 50)->comment('Tipo de notificación (proximo_7_dias, proximo_3_dias, proximo_1_dia, hoy, vencido, critico)');
            $table->string('canal', 30)->comment('Canal de envío (e.g. database, mail)');
            $table->foreignId('enviado_a')
                ->nullable()
                ->comment('Usuario receptor de la alerta')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('enviado_at')->useCurrent()->comment('Fecha y hora del envío');
            $table->jsonb('metadata')->nullable()->comment('Información complementaria del envío');

            $table->index(['mantenimiento_id', 'tipo']);
            $table->index('enviado_a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_mantenimiento_notificaciones', fn (Blueprint $t) => $t->dropIndex(['enviado_a']));
        Schema::dropIfExists('inv_mantenimiento_notificaciones');
    }
};
