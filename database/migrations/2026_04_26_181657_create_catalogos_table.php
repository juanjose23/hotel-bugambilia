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
        Schema::create('catalogos', function (Blueprint $table) {

            $table->id();
            $table->foreignId('catalogo_tipo_id')
                ->constrained('catalogo_tipos');
            $table->foreignId('padre_id')
                ->nullable()
                ->constrained('catalogos');
            $table->string('codigo', 50);
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0);
            $table->integer('estado')->default(1);
            $table->timestamps();
            $table->unique(['catalogo_tipo_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogos');
    }
};