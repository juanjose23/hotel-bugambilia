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
        Schema::create('limp_lavanderia_procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();
            $table->foreignId('producto_variante_id')
                ->nullable()
                ->constrained('producto_variantes')
                ->nullOnDelete();
            $table->foreignId('lote_id')
                ->nullable()
                ->constrained('inv_lotes')
                ->nullOnDelete();
            $table->decimal('cantidad', 12, 4);
            $table->string('estado', 30)->default('en_proceso'); // en_proceso, finalizado
            $table->timestamps();

            $table->index('producto_id');
            $table->index('producto_variante_id');
            $table->index('lote_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limp_lavanderia_procesos');
    }
};
