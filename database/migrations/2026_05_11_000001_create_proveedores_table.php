<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: PROVEEDORES (Industrial Standard)
     *
     * Implementa el registro maestro de proveedores siguiendo la Normalización 3FN.
     * Los contactos se gestionan en una tabla relacionada para permitir múltiples puntos de enlace.
     */
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->comment('Tabla maestra que registra a los proveedores de productos y servicios del hotel, vinculados a la tabla de personas.');
            $table->id()->comment('Identificador único autoincremental del proveedor');
            $table->string('codigo', 20)->unique()->comment('Código maestro del proveedor (PROV-XXXX)');
            $table->foreignId('persona_id')
                ->comment('Vínculo con el núcleo de personas (Natural/Jurídica)')
                ->constrained('personas')
                ->cascadeOnDelete();

            $table->foreignId('tipo_proveedor_id')
                ->nullable()
                ->comment('Clasificación: Nacional, Internacional, Servicio, etc.')
                ->constrained('catalogos')
                ->nullOnDelete();

            $table->string('direccion_fiscal', 255)->nullable()->comment('Domicilio legal para facturación');
            $table->text('notas')->nullable()->comment('Bitácora o anotaciones administrativas');

            $table->integer('estado')->default(1)->comment('Estatus operativo: 1=Activo, 0=Inactivo');

            $table->timestamps();
            $table->softDeletes();

            // Índices de optimización para búsquedas frecuentes
            $table->index('persona_id');
            $table->index('tipo_proveedor_id');
            $table->index('estado');
        });

        // Constraint de dominio para integridad de estados
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE proveedores ADD CONSTRAINT chk_proveedores_estado CHECK (estado IN (0,1))');
        }
    }

    public function down(): void
    {
        // Eliminar constraints en tablas que referencian proveedores antes de borrar la tabla
        if (Schema::hasTable('cotizaciones')) {
            DB::statement('ALTER TABLE cotizaciones DROP CONSTRAINT IF EXISTS cotizaciones_proveedor_id_foreign');
        }

        if (Schema::hasTable('ordenes_compra')) {
            DB::statement('ALTER TABLE ordenes_compra DROP CONSTRAINT IF EXISTS ordenes_compra_proveedor_id_foreign');
        }

        if (Schema::hasTable('inv_lotes')) {
            DB::statement('ALTER TABLE inv_lotes DROP CONSTRAINT IF EXISTS inv_lotes_proveedor_id_foreign');
        }

        if (Schema::hasTable('proveedor_contactos')) {
            DB::statement('ALTER TABLE proveedor_contactos DROP CONSTRAINT IF EXISTS proveedor_contactos_proveedor_id_foreign');
        }

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE proveedores DROP CONSTRAINT IF EXISTS chk_proveedores_estado');
        }

        Schema::dropIfExists('proveedores');
    }
};
