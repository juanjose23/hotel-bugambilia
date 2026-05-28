<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_planes_mantenimiento', function (Blueprint $table) {
            $table->date('fecha_ultimo_mantenimiento')
                ->nullable()
                ->comment('Fecha del último mantenimiento completado para este plan');
            $table->date('fecha_proximo_mantenimiento')
                ->nullable()
                ->comment('Próxima fecha programada para mantenimiento según la frecuencia');
        });

        Schema::create('act_plan_activos', function (Blueprint $table) {
            $table->comment('Relación explícita muchos a muchos entre planes y activos fijos');

            $table->foreignId('plan_id')
                ->comment('Identificador del plan de mantenimiento')
                ->constrained('inv_planes_mantenimiento')
                ->cascadeOnDelete();

            $table->foreignId('activo_id')
                ->comment('Identificador del activo fijo cubierto por el plan')
                ->constrained('inv_activos')
                ->cascadeOnDelete();

            $table->primary(['plan_id', 'activo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('act_plan_activos');

        Schema::table('inv_planes_mantenimiento', function (Blueprint $table) {
            $table->dropColumn(['fecha_ultimo_mantenimiento', 'fecha_proximo_mantenimiento']);
        });
    }
};
