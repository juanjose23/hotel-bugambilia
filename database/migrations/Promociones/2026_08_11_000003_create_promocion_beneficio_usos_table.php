<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_beneficio_usos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beneficio_id')->constrained('promocion_beneficios')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete();
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->decimal('monto_descuento', 12, 2)->default(0);
            $table->string('estado', 30)->default('aplicado')->index();
            $table->timestamp('usado_en')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'beneficio_id']);
            $table->index(['reserva_id', 'cuenta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_beneficio_usos');
    }
};
