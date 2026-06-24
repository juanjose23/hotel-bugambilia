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
        // 1. Turnos de Limpieza
        Schema::create('limp_horario_turnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->foreignId('lider_id')
                ->nullable()
                ->constrained('colaboradores')
                ->nullOnDelete();
            $table->foreignId('apoyo_id')
                ->nullable()
                ->constrained('colaboradores')
                ->nullOnDelete();
            $table->json('carritos_ids')->nullable(); // Múltiples carritos
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('lider_id');
            $table->index('apoyo_id');
        });

        // 2. Horarios / Planificación (Cabecera)
        Schema::create('limp_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')
                ->nullable()
                ->constrained('limp_horario_turnos')
                ->cascadeOnDelete();
            $table->time('hora_estimada');
            $table->string('frecuencia', 50)->default('diaria'); // diaria, semanal
            $table->string('dia_semana', 20)->nullable(); // lunes, martes...
            $table->json('checklist')->nullable(); // Plantilla de checklist
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('turno_id');
        });

        // 3. Detalles del Horario (Polimórfico)
        Schema::create('limp_horario_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')
                ->constrained('limp_horarios')
                ->cascadeOnDelete();
            $table->string('limpiable_type');
            $table->unsignedBigInteger('limpiable_id');
            $table->timestamps();

            $table->index(['limpiable_type', 'limpiable_id'], 'idx_limp_detalles_limpiable');
            $table->index('horario_id');
        });

        // 4. Ejecuciones de Limpieza del Día
        Schema::create('limp_ejecuciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')
                ->nullable()
                ->constrained('limp_horarios')
                ->nullOnDelete();
            $table->foreignId('solicitud_id')
                ->nullable()
                ->constrained('solicitud_limpiezas')
                ->nullOnDelete();
            $table->string('limpiable_type');
            $table->unsignedBigInteger('limpiable_id');
            $table->foreignId('turno_id')
                ->constrained('limp_horario_turnos')
                ->cascadeOnDelete();
            $table->foreignId('colaborador_id')
                ->nullable()
                ->constrained('colaboradores')
                ->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->integer('estado')->default(1);
            $table->json('detalles_checklist')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('recordatorio_enviado_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['limpiable_type', 'limpiable_id'], 'idx_limp_ejecuciones_limpiable');
            $table->index('horario_id');
            $table->index('turno_id');
            $table->index('colaborador_id');
            $table->index('estado');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limp_ejecuciones');
        Schema::dropIfExists('limp_horario_detalles');
        Schema::dropIfExists('limp_horarios');
        Schema::dropIfExists('limp_horario_turnos');
    }
};
