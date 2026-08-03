<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_cuenta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas')->restrictOnDelete();
            $table->unsignedBigInteger('venta_id')->nullable()->comment('FK lógica a venta (sin constraint por orden de migración)');

            /**
             * Forma de pago — respaldado por App\Enums\Cuentas\MetodoPago (int):
             * 1: Efectivo | 2: Tarjeta Crédito | 3: Tarjeta Débito | 4: Transferencia
             * 5: Depósito | 6: Pago QR | 7: Cargo a Habitación | 8: Cortesía
             * 9: Crédito Corporativo | 10: Puntos Lealtad
             */
            $table->unsignedTinyInteger('forma_pago');

            $table->foreignId('moneda_id')->nullable()->constrained('monedas')->nullOnDelete();

            $table->decimal('monto', 12, 2);
            $table->decimal('propina', 12, 2)->default(0);

            /** Número de voucher, autorización bancaria o referencia de transferencia */
            $table->string('referencia_transaccion', 100)->nullable();

            /**
             * Estado del pago — respaldado por App\Enums\Cuentas\EstadoPago (int):
             * 1: Pendiente | 2: Aplicado | 3: Anulado | 4: Reembolsado
             */
            $table->unsignedTinyInteger('estado')->default(2);

            $table->string('observaciones', 255)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cuenta_id');
            $table->index('forma_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_cuenta');
    }
};
