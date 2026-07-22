<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->foreignId('categoria_id')->nullable()->constrained('catalogos')->nullOnDelete();
            $table->foreignId('producto_receta_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->boolean('web')->default(false);
            $table->integer('estado')->default(1);
            $table->string('tiempo_preparacion', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('estado');
            $table->index('categoria_id');
            $table->index('web');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platos');
    }
};
