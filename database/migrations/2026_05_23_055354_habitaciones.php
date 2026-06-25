<?php

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
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->comment('Tabla que almacena las habitaciones del hotel');

            $table->id()->comment('Identificador único de la habitación');
            $table->string('codigo', 20)->unique()->comment('Código o número único de la habitación');
            $table->integer('numero')
                ->nullable()
                ->comment('Número visible de la habitación (ej. 101, 202)');
            $table->string('slug')->unique()->comment('Clave de la habitación');
            $table->string('nombre', 100)->comment('Nombre descriptivo de la habitación');
            $table->text('descripcion')->nullable()->comment('Descripción general de la habitación');
            $table->foreignId('categoria_id')->comment('Identificador de la categoría de habitación (ej. estándar, deluxe, suite)')->constrained('catalogos');
            $table->foreignId('ubicacion_id')->comment('Identificador de la ubicación física dentro del hotel')->constrained('ubicaciones');
            $table->tinyInteger('estado')
                ->default(1)
                ->comment('Estado de la habitación: 0=inactiva, 1=activa, 2=mantenimiento, 3=limpieza, 4=reserva, 5=ocupada');

            $table->timestamps();
            $table->softDeletes();
            $table->index('categoria_id');
            $table->index('ubicacion_id');
            $table->index('estado');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE habitaciones ADD CONSTRAINT chk_habitaciones_estado CHECK (estado IN (0, 1, 2, 3, 4, 5, 6))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habitaciones', function (Blueprint $t) {
            $t->dropIndex(['categoria_id']);
            $t->dropIndex(['ubicacion_id']);
            $t->dropIndex(['estado']);
        });
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE habitaciones DROP CONSTRAINT chk_habitaciones_estado');
            DB::statement('ALTER TABLE habitaciones ADD CONSTRAINT chk_habitaciones_estado CHECK (estado IN (0, 1, 2, 3, 4, 5))');
        }
        Schema::dropIfExists('habitaciones');
    }
};
