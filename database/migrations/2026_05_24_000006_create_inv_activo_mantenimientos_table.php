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
        Schema::create('inv_planes_mantenimiento', function (Blueprint $table) {
            $table->comment('Planes o contratos de mantenimiento preventivo/correctivo');
            $table->id()->comment('Identificador único autoincremental del plan');
            $table->string('nombre', 150)->comment('Nombre descriptivo del plan (ej. Mantenimiento anual AC)');
            $table->string('tipo', 50)->comment('Tipo de plan (preventivo, correctivo, garantia, inspeccion)');
            $table->foreignId('proveedor_id')->nullable()->comment('Proveedor externo encargado del plan')->constrained('proveedores')->nullOnDelete();
            $table->integer('frecuencia_dias')->nullable()->comment('Frecuencia en días si es un plan recurrente');
            $table->date('fecha_inicio')->comment('Fecha de inicio de vigencia del plan');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de vigencia del contrato/plan');
            $table->decimal('costo_estimado', 12, 2)->nullable()->comment('Costo estimado total o por ciclo');
            $table->foreignId('moneda_id')->nullable()->comment('Moneda del costo')->constrained('monedas')->nullOnDelete();
            $table->text('descripcion')->nullable()->comment('Detalle general de los trabajos a realizar');
            $table->integer('estado')->default(1)->comment('1=Activo, 2=Inactivo, 3=Completado');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inv_mantenimientos', function (Blueprint $table) {
            $table->comment('Tickets o registros individuales de mantenimiento ejecutados o programados');
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignId('plan_id')->nullable()->comment('Plan asociado (opcional)')->constrained('inv_planes_mantenimiento')->cascadeOnDelete();
            $table->foreignId('activo_id')->comment('Referencia al activo intervenido')->constrained('inv_activos')->cascadeOnDelete();
            $table->date('fecha_programada')->comment('Fecha para la que se programó el servicio');
            $table->date('fecha_realizada')->nullable()->comment('Fecha real en que se completó');
            $table->decimal('costo_real', 12, 2)->nullable()->comment('Costo real del servicio ejecutado');
            $table->foreignId('realizado_por_id')->nullable()->comment('Empleado técnico interno responsable')->constrained('users')->nullOnDelete();
            $table->integer('estado')->default(1)->comment('1=Programado, 2=En proceso, 3=Completado, 4=Cancelado');
            $table->text('notas')->nullable()->comment('Observaciones o problemas reportados en el ticket');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo_id', 'fecha_programada']);
            $table->index('plan_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE inv_planes_mantenimiento ADD CONSTRAINT chk_tipo_plan CHECK (tipo IN ('preventivo', 'correctivo', 'garantia', 'inspeccion'))");
            DB::statement('ALTER TABLE inv_planes_mantenimiento ADD CONSTRAINT chk_estado_plan CHECK (estado IN (1, 2, 3))');
            DB::statement('ALTER TABLE inv_mantenimientos ADD CONSTRAINT chk_estado_mantenimiento CHECK (estado IN (1, 2, 3, 4))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_mantenimientos');
        Schema::dropIfExists('inv_planes_mantenimiento');
    }
};
