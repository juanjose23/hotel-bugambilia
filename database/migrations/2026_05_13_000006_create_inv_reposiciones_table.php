<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: ÓRDENES DE REPOSICIÓN (inv_reposiciones)
     *
     * Cabecera de las órdenes de reabastecimiento entre bodegas.
     * Registra el origen (bodega que surte), el destino (bodega que recibe)
     * y el estado del ciclo de vida de la orden.
     *
     * CAMBIO CLAVE v1 → v2.1:
     *   v1 usaba: ambito + ambito_id polimórfico para el destino
     *   v2.1 usa: origen_id + destino_id explícitos (bodegas reales)
     *
     * GENERACIÓN DEL CÓDIGO:
     *   'REP-' . now()->format('Ymd') . '-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT)
     *   Ejemplo: REP-20260520-0042
     *
     * FLUJO:
     *   GenerarReposicionesBodega → estado='pendiente'
     *   ProcesarReposicion         → estado='procesada', fecha_proceso=now()
     */
    public function up(): void
    {
        Schema::create('inv_reposiciones', function (Blueprint $table) {
            $table->comment('Órdenes de reabastecimiento entre bodegas físicas. Generadas automáticamente por PAR Stock. Flujo: pendiente → procesada.');
            $table->id()->comment('Identificador único autoincremental de la orden de reposición');
            $table->string('codigo', 30)->unique()->comment('Folio único de la orden. Formato: REP-{Ymd}-{NNNN}. Generado automáticamente por GenerarReposicionesBodega.');
            $table->foreignId('origen_id')
                ->comment('Bodega que surte el stock (generalmente el Almacén General). FK → ubicaciones, cascadeOnDelete.')
                ->constrained('ubicaciones')
                ->cascadeOnDelete();
            $table->foreignId('destino_id')
                ->comment('Bodega que recibe el stock (ej: Bodega Piso 1, Bodega Spa). FK → ubicaciones, cascadeOnDelete.')
                ->constrained('ubicaciones')
                ->cascadeOnDelete();
            $table->string('estado', 20)->default('pendiente')->comment('Estado del ciclo de vida: pendiente, procesada, cancelada');
            $table->foreignId('creado_por_id')
                ->nullable()
                ->comment('Usuario o proceso automático que generó la orden. FK → users, nullOnDelete.')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('procesado_por_id')
                ->nullable()
                ->comment('Usuario que autorizó y ejecutó el surtido físico. FK → users, nullOnDelete.')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('fecha_proceso')->nullable()->comment('Fecha y hora en que se procesó físicamente la reposición');
            $table->text('notas')->nullable()->comment('Observaciones o comentarios de la orden de reposición');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_reposiciones');
    }
};
