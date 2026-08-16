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
        Schema::create('pago_transacciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pasarela_pago_id')->constrained('pasarelas_pago')->restrictOnDelete();
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            $table->foreignId('pago_cuenta_id')->nullable()->constrained('pagos_cuenta')->nullOnDelete();
            $table->string('referencia_interna', 80)->unique();
            $table->string('referencia_pasarela', 120)->nullable()->index();
            $table->string('idempotency_key', 120)->unique();
            $table->unsignedTinyInteger('estado')->default(1)->index();
            $table->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $table->foreignId('moneda_base_id')->constrained('monedas')->restrictOnDelete();
            $table->decimal('monto', 14, 2)->default(0);
            $table->decimal('monto_base', 14, 2)->default(0);
            $table->decimal('tasa_cambio', 14, 6)->default(1);
            $table->timestamp('solicitada_at')->nullable();
            $table->timestamp('autorizada_at')->nullable();
            $table->timestamp('capturada_at')->nullable();
            $table->timestamp('fallida_at')->nullable();
            $table->timestamp('reembolsada_at')->nullable();
            $table->string('codigo_respuesta', 80)->nullable();
            $table->string('mensaje_respuesta', 255)->nullable();
            $table->jsonb('request_payload')->nullable();
            $table->jsonb('response_payload')->nullable();
            $table->jsonb('webhook_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cuenta_id', 'estado']);
            $table->index(['factura_id', 'estado']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pago_transacciones ADD CONSTRAINT chk_pt_estado CHECK (estado IN (1, 2, 3, 4, 5, 6))');
            DB::statement('ALTER TABLE pago_transacciones ADD CONSTRAINT chk_pt_montos CHECK (monto >= 0 AND monto_base >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_transacciones');
    }
};
