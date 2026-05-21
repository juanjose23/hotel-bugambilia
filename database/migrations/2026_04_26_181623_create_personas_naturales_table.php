<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla personas_naturales (extensión de personas).
     * Almacena datos específicos de personas físicas: apellidos,
     * identificación oficial, sexo y fecha de nacimiento.
     * La constraint chk_identificacion_completa asegura que ambos
     * campos de identificación se proporcionen juntos o ninguno.
     */
    public function up(): void
    {
        Schema::create('personas_naturales', function (Blueprint $table) {
            $table->comment('Tabla que extiende la información común de personas con datos específicos de personas naturales/físicas.');
            $table->id()->comment('Identificador único autoincremental del registro de persona natural');
            $table->foreignId('persona_id')
                ->comment('FK a la tabla personas (relación 1:1)')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->string('primer_apellido', 100)->comment('Primer apellido del individuo');
            $table->string('segundo_apellido', 100)->nullable()->comment('Segundo apellido (opcional)');
            $table->enum('tipo_identificacion', [
                'cedula', 'dni', 'pasaporte', 'residencia', 'nit', 'ruc', 'otro',
            ])->nullable()->comment('Tipo de documento de identificación oficial');
            $table->string('numero_identificacion', 30)->nullable()->comment('Número del documento de identificación');
            $table->enum('sexo', ['M', 'F', 'O'])->nullable()->comment('Sexo: M=masculino, F=femenino, O=otro');
            $table->date('fecha_nacimiento')->nullable()->comment('Fecha de nacimiento');
            $table->timestamps();
            $table->softDeletes();
            $table->index('persona_id');
            $table->index('tipo_identificacion');
            $table->index('numero_identificacion');
            $table->unique(['tipo_identificacion', 'numero_identificacion'], 'uq_personas_naturales_identificacion');
        });
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('
            ALTER TABLE personas_naturales
            ADD CONSTRAINT chk_identificacion_completa
            CHECK (
                (tipo_identificacion IS NULL AND numero_identificacion IS NULL)
                OR
                (tipo_identificacion IS NOT NULL AND numero_identificacion IS NOT NULL)
            )
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas_naturales');
    }
};
