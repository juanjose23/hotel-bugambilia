<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla clientes del hotel.
     * Cada cliente es una persona (natural o jurídica) con un tipo
     * de cliente definido en catálogos. La relación persona-cliente
     * es 1:1 (una persona solo puede ser un cliente).
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->comment('Tabla que registra a los clientes del hotel vinculados con su información de persona y su tipo de clasificación comercial.');
            $table->id()->comment('Identificador único autoincremental del registro de cliente');
            $table->foreignId('persona_id')
                ->unique()
                ->comment('FK única a persona (relación 1:1 cliente-persona)')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->foreignId('catalogo_id')
                ->comment('Tipo de cliente definido en catálogos (ej. frecuente, corporativo, ocasional)')
                ->constrained('catalogos')
                ->restrictOnDelete();
            $table->integer('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes();
            $table->index('catalogo_id');
            $table->index('persona_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
