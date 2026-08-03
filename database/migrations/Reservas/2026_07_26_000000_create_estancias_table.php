<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('estancias')) {
            return;
        }

        Schema::create('estancias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reserva_id')->unique()->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones')->nullOnDelete();
            $table->foreignId('usuario_check_in_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_check_out_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();
            $table->unsignedSmallInteger('cantidad_llaves')->default(1);
            $table->unsignedTinyInteger('estado')->default(1)->index();
            $table->text('observaciones_entrada')->nullable();
            $table->text('observaciones_salida')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estancias');
    }
};
