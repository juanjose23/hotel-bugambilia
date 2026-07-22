<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('mesa_id')->constrained('espacios')->cascadeOnDelete();
            $table->foreignId('mesero_id')->nullable()->constrained('colaboradores')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('estado', 30)->default('abierto');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('abierto_en')->nullable();
            $table->timestamp('cerrado_en')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('mesa_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
