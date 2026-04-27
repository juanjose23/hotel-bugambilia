<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('personas_naturales', function (Blueprint $table) {

            $table->id();
            $table->foreignId('persona_id')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->string('primer_apellido', 100);
            $table->string('segundo_apellido', 100)->nullable();
            $table->enum('tipo_identificacion', [
                'cedula',
                'dni',
                'pasaporte',
                'residencia',
                'nit',
                'ruc',
                'otro'
            ])->nullable();
            $table->string('numero_identificacion', 30)->nullable();
            $table->enum('sexo', ['M', 'F', 'O'])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('persona_id');
            $table->index('tipo_identificacion');
            $table->index('numero_identificacion');
            $table->unique(['tipo_identificacion', 'numero_identificacion']);


        });
        DB::statement("
        ALTER TABLE personas_naturales
        ADD CONSTRAINT chk_identificacion_completa
        CHECK (
            (tipo_identificacion IS NULL AND numero_identificacion IS NULL)
            OR
            (tipo_identificacion IS NOT NULL AND numero_identificacion IS NOT NULL)
        )
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('personas_naturales');
    }
};