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
        Schema::create('paises', function (Blueprint $table) {

            $table->id();
            $table->string('codigo_iso2', 2)->unique();
            $table->string('codigo_iso3', 3)->unique();
            $table->string('nombre', 150);
            $table->string('codigo_telefono', 10)->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('paises');
    }
};