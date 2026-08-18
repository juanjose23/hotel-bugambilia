<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas_bitacora', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->jsonb('datos')->nullable();
            $table->timestamps();

            $table->index(['reserva_id', 'tipo'], 'idx_bitacora_reserva_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_bitacora');
    }
};
