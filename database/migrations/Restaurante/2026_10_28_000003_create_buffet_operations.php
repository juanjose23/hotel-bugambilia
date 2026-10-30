<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurante_buffets', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 150);
            $table->date('fecha_servicio');
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->restrictOnDelete();
            $table->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $table->string('modalidad', 15);
            $table->decimal('precio_persona', 14, 2)->default(0);
            $table->decimal('precio_paquete', 14, 2)->default(0);
            $table->unsignedInteger('comensales_previstos')->default(0);
            $table->unsignedInteger('comensales_reales')->default(0);
            $table->string('estado', 15)->default('abierto');
            $table->timestamp('cerrado_at')->nullable();
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->restrictOnDelete();
            $table->timestamp('cargado_at')->nullable();
            $table->unsignedInteger('personas_cobradas')->default(0);
            $table->unsignedInteger('paquetes_cobrados')->default(0);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['fecha_servicio', 'estado']);
        });
        Schema::create('restaurante_buffet_cierres', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('buffet_id')->constrained('restaurante_buffets')->restrictOnDelete();
            $table->foreignId('stock_id')->constrained('stocks')->restrictOnDelete();
            $table->decimal('cantidad_servida', 14, 4);
            $table->decimal('sobrante', 14, 4);
            $table->decimal('merma', 14, 4);
            $table->decimal('costo_consumido', 14, 2);
            $table->timestamps();
            $table->unique(['buffet_id', 'stock_id']);
        });
    }

    public function down(): void
    {
        if (DB::table('restaurante_buffets')->exists()) {
            throw new RuntimeException('No se puede revertir mientras existan operaciones de buffet.');
        }
        Schema::dropIfExists('restaurante_buffet_cierres');
        Schema::dropIfExists('restaurante_buffets');
    }
};
