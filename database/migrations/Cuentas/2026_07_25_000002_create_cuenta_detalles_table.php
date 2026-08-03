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
            $table->foreignId('cuenta_id')->constrained('cuentas')->restrictOnDelete();

            // Trazabilidad Polimórfica
            $table->nullableMorphs('origen');

            // Tipo de detalle (CategoriaConsumo)
            $table->unsignedTinyInteger('tipo_detalle')->nullable()->comment('CategoriaConsumo');

            // Contexto Físico Opcional
            $table->foreignId('espacio_id')->nullable()->comment('FK a la mesa o espacio de consumo')->constrained('espacios')->nullOnDelete();

            // Consumo puro (impuestos y cargos van en cuenta_cargos)
            $table->string('concepto', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);

            // Estado y anulación
            $table->unsignedTinyInteger('estado')->default(1)->comment('1=Activo, 0=Anulado');

            // Metadatos para auditoría
            $table->jsonb('metadatos')->nullable();

            $table->foreignId('creador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->timestamps();

            $table->index('cuenta_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_detalles');
    }
};
