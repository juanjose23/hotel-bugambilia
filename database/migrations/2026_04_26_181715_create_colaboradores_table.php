<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {

        Schema::create('colaboradores', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('persona_id')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->string('codigo', 30)->unique();
            $table->string('nss', 30)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['estado']);
        });


        Schema::create('colaborador_datos_medicos', function (Blueprint $table) {

            $table->increments('id');
            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->string('tipo_sangre', 5)->nullable();
            $table->text('alergias')->nullable();
            $table->text('enfermedades_cronicas')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
            $table->unique('colaborador_id');
        });

        Schema::create('colaborador_contactos_emergencia', function (Blueprint $table) {

            $table->increments('id');
            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('telefono', 20);
            $table->string('parentesco', 50)->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
            $table->index(['colaborador_id']);
        });

        Schema::create('colaborador_salarios', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->decimal('salario', 12, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
            $table->index(['colaborador_id', 'fecha_inicio']);
        });


        Schema::create('colaborador_cargos_historial', function (Blueprint $table) {

            $table->increments('id');
            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->foreignId('cargo_id')
                ->constrained('catalogos')
                ->cascadeOnDelete();
            $table->foreignId('departamento_id')
                ->nullable()
                ->constrained('catalogos')
                ->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
            $table->index(['colaborador_id', 'cargo_id']);
            $table->index(['departamento_id']);
        });


        Schema::create('colaborador_documentos', function (Blueprint $table) {

            $table->increments('id');
            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->string('archivo');
            $table->timestamps();
            $table->index(['colaborador_id']);
        });

    }


    public function down(): void
    {

        Schema::dropIfExists('colaborador_documentos');
        Schema::dropIfExists('colaborador_cargos_historial');
        Schema::dropIfExists('colaborador_salarios');
        Schema::dropIfExists('colaborador_datos_medicos');
        Schema::dropIfExists('colaboradores');

    }

};