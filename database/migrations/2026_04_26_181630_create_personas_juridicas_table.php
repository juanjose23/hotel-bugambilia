<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla personas_juridicas (extensión de personas).
     * Almacena datos específicos de empresas y organizaciones:
     * razón social, identificación fiscal y fecha de constitución.
     * La constraint chk_identificacion_completa asegura coherencia
     * en los campos de identificación.
     */
    public function up(): void
    {
        Schema::create('personas_juridicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')
                ->comment('FK a la tabla personas (relación 1:1)')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->string('razon_social', 150)->comment('Nombre o razón social de la empresa');
            $table->enum('tipo_identificacion', [
                'ruc', 'nit', 'tax_id', 'registro_comercial', 'otro',
            ])->nullable()->comment('Tipo de identificación fiscal o comercial');
            $table->string('numero_identificacion', 30)->nullable()->comment('Número de identificación fiscal/comercial');
            $table->date('fecha_constitucion')->nullable()->comment('Fecha de constitución legal de la empresa');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tipo_identificacion', 'numero_identificacion'], 'uq_personas_juridicas_identificacion');
        });

        DB::statement('ALTER TABLE personas_juridicas
        ADD CONSTRAINT chk_identificacion_completa
        CHECK (
            (tipo_identificacion IS NULL AND numero_identificacion IS NULL)
            OR
            (tipo_identificacion IS NOT NULL AND numero_identificacion IS NOT NULL)
        )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas_juridicas');
    }
};
