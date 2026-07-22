<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HISTORIAL DE COMPRAS (Purchase Audit Log)
     *
     * Registra el historial de transiciones de estado de todos los documentos
     * del flujo de compras (solicitudes, órdenes, recepciones).
     */
    public function up(): void
    {
        Schema::create('compra_historial', function (Blueprint $table) {
            $table->comment('Tabla de historial y auditoría de transiciones de estados de documentos en el flujo de compras (requisiciones, órdenes de compra, etc.).');
            $table->id()->comment('Identificador único autoincremental de la entrada del historial de compras');
            $table->string('model_type')->comment('Nombre del modelo o clase afectada (ej. SolicitudCompra, OrdenCompra)');
            $table->unsignedBigInteger('model_id')->comment('Identificador del registro del modelo de compra afectado');
            $table->string('estado_anterior')->nullable()->comment('Código o nombre del estado previo a la transición');
            $table->string('estado_nuevo')->comment('Código o nombre del estado resultante tras la transición');
            $table->foreignId('usuario_id')->nullable()->comment('Usuario responsable de realizar y autorizar el cambio de estado')->constrained('users');
            $table->text('comentario')->nullable()->comment('Justificación o comentarios adicionales ingresados sobre el cambio de estado');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['model_type', 'model_id']);
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::table('compra_historial', fn (Blueprint $t) => $t->dropIndex(['usuario_id']));
        Schema::dropIfExists('compra_historial');
    }
};
