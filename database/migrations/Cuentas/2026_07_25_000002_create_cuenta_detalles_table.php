<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuenta_detalles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas')->cascadeOnDelete();

            // Trazabilidad Polimórfica (Ej: Plato, HabitacionNoche, ServicioSpa, etc.)
            $table->nullableMorphs('origen');

            // Contexto Físico Opcional de Consumo (Útil para restaurantes o ubicaciones)
            $table->foreignId('espacio_id')->nullable()->comment('FK a la mesa o espacio de consumo')->constrained('espacios')->nullOnDelete();

            // Valores Económicos del Renglón (Desglose Fiscal Obligatorio)
            $table->string('concepto', 255);
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2); // cantidad * precio_unitario
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2); // subtotal - descuento + impuesto

            // Metadatos flexibles para auditoría técnica (JSON)
            $table->json('metadatos')->nullable();

            $table->foreignId('creador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Índices de rendimiento
            $table->index('cuenta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_detalles');
    }
};
