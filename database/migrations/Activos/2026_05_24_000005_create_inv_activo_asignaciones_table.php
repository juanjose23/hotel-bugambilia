<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inv_activo_asignaciones', function (Blueprint $table) {
            $table->comment('Historial de asignaciones físicas y ubicación de activos (habitaciones, bodegas, espacios)');
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignId('activo_id')->comment('Referencia al activo asignado')->constrained('inv_activos')->cascadeOnDelete();
            $table->string('asignable_type')->comment('Tipo del modelo asignado (Habitacion, Ubicacion, etc.)');
            $table->bigInteger('asignable_id')->comment('Identificador único del modelo asignado');
            $table->date('fecha_inicio')->comment('Fecha de inicio de la asignación física');
            $table->date('fecha_fin')->nullable()->comment('Fecha de finalización de la asignación (null indica vigente)');
            $table->string('motivo', 255)->nullable()->comment('Razón del movimiento o asignación');
            $table->foreignId('asignado_por_id')->nullable()->comment('Usuario que realiza la asignación')->constrained('users')->nullOnDelete();
            $table->foreignId('recibido_por_id')->nullable()->comment('Usuario que recibe/confirma en destino')->constrained('users')->nullOnDelete();
            $table->integer('estado')->default(1)->comment('1=Vigente, 2=Cerrada, 3=En tránsito');
            $table->text('notas')->nullable()->comment('Notas u observaciones adicionales');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo_id', 'fecha_fin']);
            $table->index(['asignable_type', 'asignable_id', 'fecha_fin']);
            $table->index('asignado_por_id');
            $table->index('recibido_por_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inv_activo_asignaciones ADD CONSTRAINT chk_estado_asignaciones CHECK (estado IN (1, 2, 3))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_activo_asignaciones', function (Blueprint $t) {
            $t->dropIndex(['asignado_por_id']);
            $t->dropIndex(['recibido_por_id']);
        });
        Schema::dropIfExists('inv_activo_asignaciones');
    }
};
