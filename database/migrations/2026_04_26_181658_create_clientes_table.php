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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')
                ->unique()
                ->constrained('personas')
                ->cascadeOnDelete();
            $table->foreignId('catalogo_id')
                ->constrained('catalogos')
                ->restrictOnDelete();
            $table->integer('estado')->default(1);
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
        //
        Schema::dropIfExists('clientes');
    }
};