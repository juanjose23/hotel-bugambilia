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
        Schema::create('facturas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_serie_id')->constrained('factura_series')->restrictOnDelete();
            $table->foreignId('factura_autorizacion_dgi_id')->nullable()->constrained('factura_autorizaciones_dgi')->nullOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();

            $table->unsignedTinyInteger('tipo')->default(1)->index();
            $table->unsignedTinyInteger('estado')->default(1)->index();
            $table->string('numero', 50);
            $table->unsignedBigInteger('numero_correlativo');
            $table->timestamp('fecha_emision')->nullable()->index();
            $table->date('fecha_vencimiento')->nullable()->index();

            $table->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $table->foreignId('moneda_base_id')->constrained('monedas')->restrictOnDelete();
            $table->foreignId('tasa_cambio_id')->nullable()->constrained('tasas_cambio')->nullOnDelete();
            $table->decimal('tasa_cambio', 14, 6)->default(1);

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento_total', 14, 2)->default(0);
            $table->decimal('iva_total', 14, 2)->default(0);
            $table->decimal('servicio_total', 14, 2)->default(0);
            $table->decimal('propina_total', 14, 2)->default(0);
            $table->decimal('recargo_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->decimal('subtotal_base', 14, 2)->default(0);
            $table->decimal('iva_total_base', 14, 2)->default(0);
            $table->decimal('total_base', 14, 2)->default(0);

            $table->jsonb('datos_receptor')->nullable();
            $table->jsonb('meta_datos')->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->string('hash_documento', 128)->nullable()->unique();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamp('anulada_at')->nullable();
            $table->foreignId('emitida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anulada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['factura_serie_id', 'numero_correlativo'], 'uq_factura_serie_correlativo');
            $table->unique(['factura_serie_id', 'numero'], 'uq_factura_serie_numero');
            $table->index(['venta_id', 'estado']);
            $table->index(['cliente_id', 'fecha_emision']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE facturas ADD CONSTRAINT chk_facturas_tipo CHECK (tipo IN (1, 2, 3))');
            DB::statement('ALTER TABLE facturas ADD CONSTRAINT chk_facturas_estado CHECK (estado IN (1, 2, 3, 4))');
            DB::statement('ALTER TABLE facturas ADD CONSTRAINT chk_facturas_totales CHECK (subtotal >= 0 AND iva_total >= 0 AND total >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
