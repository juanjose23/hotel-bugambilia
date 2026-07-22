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
        Schema::create('inv_activo_bajas', function (Blueprint $table) {
            $table->comment('Documento formal para el registro de bajas definitivas de activos fijos');
            $table->id()->comment('Identificador único autoincremental de la baja');
            $table->string('codigo', 30)->unique()->comment('Folio del acta de baja (ej. BAJA-2026-0001)');
            $table->foreignId('activo_id')->comment('Referencia al activo fidedigno dado de baja')->constrained('inv_activos')->cascadeOnDelete();
            $table->date('fecha_baja')->comment('Fecha oficial de la baja');
            $table->string('motivo_tipo', 50)->comment('Categoría de la baja (obsolescencia, daño_irreparable, robo, perdida, donacion, venta)');
            $table->text('motivo_detalle')->comment('Explicación y justificación técnica del motivo de la baja');
            $table->decimal('valor_residual', 12, 2)->nullable()->comment('Valor residual o de venta de recuperación si aplica');
            $table->foreignId('aprobado_por_id')->nullable()->comment('Usuario administrador que aprueba la baja')->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por_id')->comment('Usuario que registra la baja')->constrained('users')->cascadeOnDelete();
            $table->string('documento_soporte', 255)->nullable()->comment('Ruta física al archivo digital de soporte (acta firmada, denuncia)');
            $table->timestamps();
            $table->softDeletes();
            $table->index('activo_id');
            $table->index('aprobado_por_id');
            $table->index('creado_por_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE inv_activo_bajas ADD CONSTRAINT chk_motivo_tipo_bajas CHECK (motivo_tipo IN ('obsolescencia', 'daño_irreparable', 'robo', 'perdida', 'donacion', 'venta'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_activo_bajas', function (Blueprint $t) {
            $t->dropIndex(['activo_id']);
            $t->dropIndex(['aprobado_por_id']);
            $t->dropIndex(['creado_por_id']);
        });
        Schema::dropIfExists('inv_activo_bajas');
    }
};
