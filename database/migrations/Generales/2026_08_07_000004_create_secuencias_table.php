<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('secuencias')) {
            Schema::create('secuencias', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 50)->nullable();
                $table->string('tipo', 50)->nullable();
                $table->integer('anio')->nullable();
                $table->unsignedBigInteger('ultimo_valor')->default(0);
                $table->unsignedBigInteger('ultimo_numero')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias');
    }
};
