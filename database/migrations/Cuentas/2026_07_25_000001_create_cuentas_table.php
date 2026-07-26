<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table): void {
            $table->id();
            $table->string('numero_cuenta', 50)->unique();
            /**
             * Tipo de cuenta — respaldado por App\Enums\Cuentas\TipoCuenta (int):
             * 1: Estancia | 2: Restaurante Directo POS | 3: Venta por Servicio
             */
            $table->unsignedTinyInteger('tipo_cuenta')->default(1);

            /**
             * Estado del ciclo de vida — respaldado por App\Enums\Cuentas\EstadoCuenta (int):
             * 1: Solicitada | 2: Abierta | 3: Bloqueada | 4: Pendiente de Pago | 5: Cerrada | 6: Anulada
             */
            $table->unsignedTinyInteger('estado')->default(2)->index();

            // Titular / Contexto Operativo
            $table->foreignId('cliente_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->foreignId('estancia_id')->nullable()->constrained('estancias')->nullOnDelete();
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete();

            // Balances Consolidados (Cacheados para alto rendimiento contable)
            $table->decimal('limite_autorizado', 12, 2)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento_total', 12, 2)->default(0);
            $table->decimal('impuesto_total', 12, 2)->default(0);
            $table->decimal('propina_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('total_pagado', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);

            // Auditoría de Tiempos y Usuarios
            $table->timestamp('abierta_at');
            $table->timestamp('cerrada_at')->nullable();
            $table->foreignId('abierta_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Índices compuestos para optimizar reportes y búsquedas frecuentes
            $table->index(['cliente_id', 'estado']);
            $table->index(['estado', 'tipo_cuenta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
