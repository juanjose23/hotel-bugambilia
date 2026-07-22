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
        Schema::create('inv_registro_individualizacion', function (Blueprint $table) {
            $table->comment('Encabezado del proceso de individualización de activos fijos recibidos en compras');
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignId('recepcion_item_id')->unique()->comment('Referencia al ítem de recepción origen')->constrained('recepcion_items')->cascadeOnDelete();
            $table->foreignId('producto_id')->comment('Referencia al producto base')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('producto_variante_id')->nullable()->comment('Referencia a la variante específica')->constrained('producto_variantes')->nullOnDelete();
            $table->integer('cantidad_total')->comment('Cantidad total de unidades a individualizar');
            $table->integer('cantidad_registrada')->default(0)->comment('Cantidad de unidades ya registradas individualmente');
            $table->integer('estado')->default(1)->comment('1=Pendiente, 2=En proceso, 3=Completado');
            $table->foreignId('registrado_por_id')->nullable()->comment('Usuario responsable de completar la individualización')->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_completado')->nullable()->comment('Fecha y hora de finalización del registro');
            $table->text('notes')->nullable()->comment('Notas u observaciones del operador');
            $table->timestamps();

            $table->index('estado');
            $table->index(['producto_id', 'estado']);
            $table->index('producto_variante_id');
            $table->index('registrado_por_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inv_registro_individualizacion ADD CONSTRAINT chk_estado_indiv CHECK (estado IN (1, 2, 3))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_registro_individualizacion', function (Blueprint $t) {
            $t->dropIndex(['producto_variante_id']);
            $t->dropIndex(['registrado_por_id']);
        });
        Schema::dropIfExists('inv_registro_individualizacion');
    }
};
