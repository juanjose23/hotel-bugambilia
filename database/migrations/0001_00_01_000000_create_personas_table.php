<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create("personas", function (Blueprint $table) {
            $table->id();
            $table->string('primer_nombre', 100);
            $table->string('segundo_nombre', 100)->nullable();
            $table->foreignId('pais_id')
                ->nullable()
                ->constrained('paises')
                ->nullOnDelete();
            $table->enum('tipo_persona', ['natural', 'juridica']);
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_persona');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists("personas");
    }
};