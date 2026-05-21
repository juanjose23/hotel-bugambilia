<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: SOLICITUDES DE COMPRA (Internal Demand)
     *
     * Gestiona el flujo inicial de requisiciones de materiales y servicios.
     * Implementa trazabilidad departamental y control de estados de aprobación.
     */
    public function up(): void
    {
        Schema::create('solicitudes_compra', function (Blueprint $table) {
            $table->comment('Tabla que registra las solicitudes de compra internas (requisiciones) emitidas por los departamentos.');
            $table->id()->comment('Identificador único autoincremental de la solicitud de compra');
            $table->string('codigo', 30)->unique()->comment('Código de documento (S-SIGLAS-NNN)');

            $table->foreignId('colaborador_id')
                ->comment('Responsable de la emisión de la solicitud')
                ->constrained('colaboradores')
                ->cascadeOnDelete();

            $table->foreignId('departamento_solicitante_id')
                ->nullable()
                ->comment('Centro de costo / Departamento que origina la demanda')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->date('fecha_solicitud')->comment('Fecha de registro en sistema');
            $table->date('fecha_necesita')->nullable()->comment('Fecha límite requerida por operación');

            $table->text('motivo')->nullable()->comment('Justificación operativa del gasto');
            $table->integer('estado')->default(1)->comment('Estado del ciclo de vida (Enum EstadoSolicitud)');
            $table->text('notas')->nullable()->comment('Observaciones de revisión por el área de compras');

            $table->timestamps();
            $table->softDeletes();

            $table->index('colaborador_id');
            $table->index('departamento_solicitante_id');
            $table->index('estado');
        });

        // Constraint de dominio alineado con el Enum EstadoSolicitud (1=Borrador, 2=Pendiente, 3=Aprobada, 4=Rechazada, 5=Cancelada)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE solicitudes_compra ADD CONSTRAINT chk_solicitudes_estado CHECK (estado IN (1,2,3,4,5))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_compra');
    }
};
