<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla catalogos (catálogos dinámicos del sistema).
     * Almacena valores de catálogos clasificados por tipo.
     * Soporta jerarquía padre-hijo (autoreferencia) para estructuras
     * de árbol. Ejemplos: cargos, departamentos, marcas, categorías,
     * unidades de medida, tipos de cliente, proveedores, etc.
     */
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->comment('Tabla que almacena los valores y elementos individuales de todos los catálogos paramétricos dinámicos del sistema.');
            $table->id()->comment('Identificador único autoincremental del elemento de catálogo');
            $table->foreignId('catalogo_tipo_id')
                ->comment('FK al tipo de catálogo al que pertenece este registro')
                ->constrained('catalogo_tipos');
            $table->foreignId('padre_id')
                ->nullable()
                ->comment('FK autoreferenciada para jerarquías padre-hijo')
                ->constrained('catalogos');
            $table->string('codigo', 50)->comment('Código único dentro del tipo de catálogo');
            $table->string('nombre', 200)->comment('Nombre del valor de catálogo');
            $table->text('descripcion')->nullable()->comment('Descripción opcional del valor');
            $table->integer('orden')->default(0)->comment('Orden de visualización dentro del tipo');
            $table->integer('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->unique(['catalogo_tipo_id', 'codigo'], 'uq_catalogos_tipo_codigo');
            $table->index('catalogo_tipo_id');
            $table->index('padre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogos', fn (Blueprint $t) => $t->dropIndex(['catalogo_tipo_id']));
        Schema::table('catalogos', fn (Blueprint $t) => $t->dropIndex(['padre_id']));
        Schema::dropIfExists('catalogos');
    }
};
