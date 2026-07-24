<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->boolean('solicita_cuenta')->default(false)->after('ninos');
            $table->decimal('limite_cuenta_solicitado', 12, 2)->nullable()->after('solicita_cuenta');
        });

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

        Schema::create('cuentas_estancia', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('estancia_id')->unique()->constrained('estancias')->cascadeOnDelete();
            $table->string('numero_folio', 50)->unique();
            $table->unsignedTinyInteger('estado')->default(1)->index();
            $table->decimal('limite_autorizado', 12, 2)->nullable();
            $table->decimal('total_cargos', 12, 2)->default(0);
            $table->decimal('total_pagos', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->timestamp('abierta_at');
            $table->timestamp('cerrada_at')->nullable();
            $table->foreignId('abierta_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('movimientos_cuenta_estancia', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuenta_estancia_id')->constrained('cuentas_estancia')->cascadeOnDelete();
            $table->string('tipo', 20)->index();
            $table->string('concepto', 200);
            $table->decimal('monto', 12, 2);
            $table->nullableMorphs('origen');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->index(['cuenta_estancia_id', 'created_at'], 'mov_cuenta_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_cuenta_estancia');
        Schema::dropIfExists('cuentas_estancia');
        Schema::dropIfExists('estancias');

        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropColumn(['solicita_cuenta', 'limite_cuenta_solicitado']);
        });
    }
};
