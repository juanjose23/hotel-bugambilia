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
        Schema::create('factura_detalles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete();
            $table->foreignId('venta_detalle_id')->nullable()->constrained('venta_detalles')->nullOnDelete();
            $table->string('codigo', 60)->nullable();
            $table->string('concepto', 255);
            $table->string('unidad_medida', 30)->default('UND');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('iva_porcentaje', 7, 4)->default(15);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total_linea', 14, 2)->default(0);
            $table->jsonb('meta_datos')->nullable();
            $table->timestamps();

            $table->index('factura_id');
            $table->index('venta_detalle_id');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE factura_detalles ADD CONSTRAINT chk_fd_cantidad CHECK (cantidad > 0)');
            DB::statement('ALTER TABLE factura_detalles ADD CONSTRAINT chk_fd_totales CHECK (subtotal >= 0 AND iva >= 0 AND total_linea >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_detalles');
    }
};
