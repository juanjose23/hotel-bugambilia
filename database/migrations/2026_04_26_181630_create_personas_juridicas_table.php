<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('personas_juridicas', function (Blueprint $table) {

            $table->id();
            $table->foreignId('persona_id')
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->string('razon_social', 150);
            $table->enum('tipo_identificacion', [
                'ruc',
                'nit',
                'tax_id',
                'registro_comercial',
                'otro'
            ])->nullable();
            $table->string('numero_identificacion', 30)->nullable();
            $table->date('fecha_constitucion')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tipo_identificacion', 'numero_identificacion']);
        });

        DB::statement("ALTER TABLE personas_juridicas
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
        Schema::dropIfExists("personas_juridicas");
    }
};