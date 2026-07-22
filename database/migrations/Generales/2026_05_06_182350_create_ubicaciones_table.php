<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla ubicaciones para la jerarquía física del hotel.
     * Estructura de árbol con niveles: edificio > piso > sector > zona.
     * Se usa para asignar inventario, colaboradores o servicios
     * a ubicaciones físicas específicas dentro del hotel.
     */
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->comment('Tabla que define la estructura física y lógica de ubicaciones y almacenes en el hotel.');
            $table->id()->comment('Identificador único autoincremental de la ubicación');
            $table->foreignId('padre_id')
                ->nullable()
                ->comment('FK autoreferenciada. Nodo padre en la jerarquía (edificio > piso > sector > zona).')
                ->constrained('ubicaciones')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('tipo', 50)
                ->comment('Nivel jerárquico físico o tipo de almacén (ej. edificio, piso, sector, zona, almacen, bodega, estante, nivel, posicion, etc.).');
            $table->string('nombre', 150)
                ->comment('Nombre descriptivo de la ubicación.');
            $table->text('descripcion')
                ->nullable()
                ->comment('Información adicional de la ubicación.');
            $table->integer('orden')
                ->default(0)
                ->comment('Número para ordenar las ubicaciones dentro del mismo nivel jerárquico.');
            $table->integer('estado')->default(1)
                ->comment('1 = activo, 0 = inactivo, 3 = en mantenimiento');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['padre_id', 'orden']);
            $table->index('padre_id');
            $table->index('tipo');
            $table->index(['padre_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
