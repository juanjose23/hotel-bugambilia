<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('limp_sustituciones_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejecucion_id')
                ->constrained('limp_ejecuciones')
                ->cascadeOnDelete();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();
            $table->foreignId('sustituto_producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();
            $table->foreignId('producto_variante_id')
                ->nullable()
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->foreignId('sustituto_variante_id')
                ->nullable()
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->decimal('cantidad', 12, 4);
            $table->timestamps();

            $table->index('ejecucion_id');
            $table->index('producto_id');
            $table->index('sustituto_producto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limp_sustituciones_stock');
    }
};
