<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesos_cocina', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('producto_origen_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('variante_origen_id')->nullable()->constrained('producto_variantes')->nullOnDelete();
            $table->decimal('cantidad_procesada', 10, 3);
            $table->decimal('costo_total', 10, 2);
            $table->foreignId('realizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos_cocina');
    }
};
