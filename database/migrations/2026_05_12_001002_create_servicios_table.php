<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: Servicios
     *
     * Registra los servicios adicionales que se pueden ofrecer a los huéspedes.
     * Estos servicios no son consumibles y no se registran en inv_stock.
     * Ejemplos: spa, gimnasio, transporte, etc.
     */
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->comment('Servicios adicionales que se pueden ofrecer a los huéspedes, como spa, gimnasio, transporte, etc. No son consumibles: no se registran en inv_stock.');
            $table->id()->comment('Identificador único autoincremental del registro');
            $table->string('codigo', 20)->unique()->comment('Código único del servicio para referencia interna');
            $table->string('nombre', 100)->comment('Nombre del servicio');
            $table->foreignId('categoria_id')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->text('descripcion')->nullable()->comment('Descripción detallada del servicio');
            $table->string('icono', 50)->nullable()->comment('Nombre del icono (Heroicon/Lucide) para mostrar en la web');
            $table->integer('estado')->default(1)->comment('Estado del servicio: 1=Activo, 2=En Reparación, 3=Inactivo');
            $table->timestamps();
            $table->softDeletes();
            $table->index('estado');

        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE servicios ADD CONSTRAINT servicios_estado_check CHECK (estado IN (1, 2, 3))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
