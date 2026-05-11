<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea las tablas del módulo de colaboradores (empleados).
     * Colaboradores: registro principal del empleado.
     * Datos médicos: información de salud (tipo sangre, alergias).
     * Contactos de emergencia: personas a contactar en caso de incidente.
     * Salarios: historial salarial del colaborador.
     * Cargos: historial de cargos y departamentos asignados.
     * Documentos: archivos digitales asociados (contratos, certificados).
     */
    public function up(): void
    {

        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')
                ->comment('FK a la persona (relación 1:1 colaborador-persona)')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->string('codigo', 30)->unique()->comment('Código único interno de empleado (ej. EMP-001)');
            $table->string('nss', 30)->nullable()->comment('Número de Seguro Social (o equivalente)');
            $table->date('fecha_ingreso')->nullable()->comment('Fecha de ingreso a la empresa');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['estado']);
        });

        Schema::create('colaborador_datos_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')
                ->comment('FK al colaborador (relación 1:1)')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->string('tipo_sangre', 5)->nullable()->comment('Tipo de sangre (A+, O-, AB+, etc.)');
            $table->text('alergias')->nullable()->comment('Alergias registradas del colaborador');
            $table->text('enfermedades_cronicas')->nullable()->comment('Enfermedades crónicas o condiciones preexistentes');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->unique('colaborador_id');
        });

        Schema::create('colaborador_contactos_emergencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')
                ->comment('FK al colaborador')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->string('nombre', 150)->comment('Nombre completo del contacto de emergencia');
            $table->string('telefono', 20)->comment('Teléfono del contacto de emergencia');
            $table->string('parentesco', 50)->nullable()->comment('Parentesco o relación con el colaborador');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            $table->timestamps();
            $table->index(['colaborador_id']);
        });

        Schema::create('colaborador_salarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')
                ->comment('FK al colaborador')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->decimal('salario', 12)->comment('Monto del salario en la moneda local');
            $table->date('fecha_inicio')->comment('Fecha a partir de la cual rige este salario');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de vigencia (null = vigente)');
            $table->tinyInteger('estado')->default(1)->comment('1=activo/vigente, 0=inactivo/histórico');
            $table->timestamps();
            $table->index(['colaborador_id', 'fecha_inicio']);
        });

        Schema::create('colaborador_cargos_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')
                ->comment('FK al colaborador')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->foreignId('cargo_id')
                ->comment('Cargo ocupado (valor del catálogo de cargos)')
                ->constrained('catalogos')
                ->cascadeOnDelete();
            $table->foreignId('departamento_id')
                ->nullable()
                ->comment('Departamento asignado (valor del catálogo de departamentos)')
                ->constrained('catalogos')
                ->nullOnDelete();
            $table->date('fecha_inicio')->comment('Fecha de inicio en el cargo');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin en el cargo (null = cargo actual)');
            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo/histórico');
            $table->timestamps();
            $table->index(['colaborador_id', 'cargo_id']);
            $table->index(['departamento_id']);
        });

        Schema::create('colaborador_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')
                ->comment('FK al colaborador')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->string('tipo', 50)->comment('Tipo de documento (contrato, cv, certificado, etc.)');
            $table->string('archivo')->comment('Ruta al archivo subido en storage');
            $table->timestamps();
            $table->index(['colaborador_id']);
        });

    }

    public function down(): void
    {

        Schema::dropIfExists('colaborador_documentos');
        Schema::dropIfExists('colaborador_cargos_historial');
        Schema::dropIfExists('colaborador_contactos_emergencia');
        Schema::dropIfExists('colaborador_salarios');
        Schema::dropIfExists('colaborador_datos_medicos');
        Schema::dropIfExists('colaboradores');

    }
};
