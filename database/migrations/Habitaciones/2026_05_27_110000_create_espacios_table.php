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
        Schema::create('espacios', function (Blueprint $table) {
            $table->comment('Tabla que almacena los espacios configurables del hotel (mesas, salones, gym, etc.) con jerarquía.');

            $table->id();
            $table->foreignId('padre_id')
                ->nullable()
                ->comment('FK autoreferenciada. Espacio contenedor (ej. Restaurante es padre de Mesa 1)')
                ->constrained('espacios')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('codigo', 50)->unique()->comment('Código identificador único del espacio');
            $table->string('nombre', 150)->comment('Nombre descriptivo del espacio');
            $table->text('descripcion')->nullable()->comment('Detalle o descripción opcional');
            $table->string('tipo', 50)->comment('Tipo de espacio: restaurante, mesa, gym, salon, spa, piscina, cancha, etc.');
            $table->integer('capacidad_personas')->default(1)->comment('Capacidad máxima de personas');

            $table->foreignId('ubicacion_id')
                ->nullable()
                ->comment('Ubicación física general en el hotel (opcional si la heredan de su padre)')
                ->constrained('ubicaciones');

            $table->tinyInteger('estado')->default(1)->comment('0=Inactivo, 1=Disponible, 2=Mantenimiento, 3=Limpieza, 4=Reservado, 5=Ocupado, 6=Sucio');
            $table->integer('orden')->default(0)->comment('Orden de visualización');
            $table->json('meta_datos')->nullable()->comment('Atributos dinámicos configurables por tipo de espacio');

            $table->timestamps();
            $table->softDeletes();

            $table->index('padre_id');
            $table->index('tipo');
            $table->index('estado');
            $table->index('ubicacion_id');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE espacios ADD CONSTRAINT chk_espacios_estado CHECK (estado IN (0, 1, 2, 3, 4, 5, 6))');
            DB::statement('ALTER TABLE espacios ADD CONSTRAINT chk_espacios_capacidad CHECK (capacidad_personas >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('espacios', fn (Blueprint $t) => $t->dropIndex(['ubicacion_id']));
        Schema::dropIfExists('espacios');
    }
};
