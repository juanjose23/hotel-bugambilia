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
        Schema::create('inv_activos', function (Blueprint $table) {
            $table->comment('Registro único por cada unidad física de activo fijo individualizado');
            $table->id()->comment('Identificador único autoincremental');
            $table->string('codigo_inventario', 50)->unique()->comment('Código único de inventario del activo (ej. TV-2026-0001)');
            $table->foreignId('individualizacion_id')->nullable()->comment('Referencia al proceso de individualización origen')->constrained('inv_registro_individualizacion')->cascadeOnDelete();
            $table->foreignId('recepcion_item_id')->nullable()->comment('Referencia al ítem de recepción física')->constrained('recepcion_items')->nullOnDelete();
            $table->foreignId('producto_id')->comment('Referencia al producto')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('producto_variante_id')->nullable()->comment('Referencia a la variante')->constrained('producto_variantes')->nullOnDelete();
            $table->string('nombre_descriptivo', 200)->nullable()->comment('Nombre descriptivo específico del activo (ej. TV Suite 101)');
            $table->string('numero_serie', 100)->nullable()->comment('Número de serie proporcionado por el fabricante');
            $table->date('fecha_adquisicion')->comment('Fecha de adquisición o entrada física al hotel');
            $table->decimal('costo_adquisicion', 14, 2)->nullable()->comment('Costo de adquisición unitario del activo');
            $table->foreignId('moneda_id')->nullable()->comment('Moneda de adquisición')->constrained('monedas')->nullOnDelete();
            $table->foreignId('proveedor_id')->nullable()->comment('Proveedor del activo')->constrained('proveedores')->nullOnDelete();
            $table->integer('vida_util_meses')->nullable()->comment('Vida útil estimada en meses del activo');
            $table->date('fecha_garantia_fin')->nullable()->comment('Fecha de vencimiento de la garantía del activo');
            $table->integer('estado')->default(1)->comment('1=Activo, 2=En mantenimiento, 3=Dado de baja, 4=Extraviado, 5=En tránsito, 6=Repuesto');
            $table->text('notas')->nullable()->comment('Notas u observaciones internas');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['producto_id', 'estado']);
            $table->index('individualizacion_id');
            $table->index('numero_serie');
            $table->index('recepcion_item_id');
            $table->index('producto_variante_id');
            $table->index('moneda_id');
            $table->index('proveedor_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inv_activos ADD CONSTRAINT chk_estado_activos CHECK (estado IN (1, 2, 3, 4, 5, 6))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inv_activos', function (Blueprint $t) {
            $t->dropIndex(['recepcion_item_id']);
            $t->dropIndex(['producto_variante_id']);
            $t->dropIndex(['moneda_id']);
            $t->dropIndex(['proveedor_id']);
        });
        Schema::dropIfExists('inv_activos');
    }
};
