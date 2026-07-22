<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla personas, raíz del sistema de personas del hotel.
     * Esta tabla almacena los datos comunes compartidos entre personas
     * naturales y jurídicas (clientes, colaboradores, proveedores).
     * El campo tipo_persona determina qué tabla hija extiende el registro.
     */
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->comment('Tabla principal de personas que almacena datos comunes compartidos entre personas naturales y jurídicas.');
            $table->id()->comment('Identificador único autoincremental de la persona');
            $table->string('primer_nombre', 100)->comment('Primer nombre de la persona o razón social abreviada');
            $table->string('segundo_nombre', 100)->nullable()->comment('Segundo nombre (opcional)');
            $table->foreignId('pais_id')
                ->nullable()
                ->comment('País de residencia u origen')
                ->constrained('paises')
                ->nullOnDelete();
            $table->enum('tipo_persona', ['natural', 'juridica'])->comment('Define si es persona natural (física) o jurídica (empresa)');
            $table->string('telefono', 20)->nullable()->comment('Teléfono de contacto principal');
            $table->string('direccion', 255)->nullable()->comment('Dirección física del domicilio');

            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_persona');
            $table->index('pais_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', fn (Blueprint $t) => $t->dropIndex(['pais_id']));
        Schema::dropIfExists('personas');
    }
};
