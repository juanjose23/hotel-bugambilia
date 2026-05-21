<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla proveedor_contactos para almacenar múltiples
     * contactos adicionales por proveedor (ej. comercial, finanzas,
     * logística, etc.). El campo principal indica el contacto por defecto.
     */
    public function up(): void
    {
        Schema::create('proveedor_contactos', function (Blueprint $table) {
            $table->comment('Tabla que registra las personas de contacto directo asociadas a cada proveedor.');
            $table->id()->comment('Identificador único autoincremental del contacto del proveedor');
            $table->foreignId('proveedor_id')
                ->comment('FK al proveedor')
                ->constrained('proveedores')
                ->cascadeOnDelete();
            $table->string('nombre', 150)->comment('Nombre completo del contacto');
            $table->string('cargo', 100)->nullable()->comment('Cargo o departamento');
            $table->string('telefono', 20)->nullable()->comment('Teléfono directo');
            $table->string('email', 100)->nullable()->comment('Correo electrónico');
            $table->boolean('principal')->default(false)->comment('Indica si es el contacto principal');
            $table->timestamps();
            $table->softDeletes();

            $table->index('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_contactos');
    }
};
