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
        Schema::create('venta_detalles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->restrictOnDelete();
            $table->string('concepto', 255);
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            // Fotografía histórica de cargos aplicados a este renglón
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('servicio', 12, 2)->default(0);
            $table->decimal('propina', 12, 2)->default(0);
            $table->decimal('recargo', 12, 2)->default(0);
            $table->decimal('total_linea', 12, 2)->default(0);

            // Trazabilidad al origen
            $table->nullableMorphs('origen');

            $table->timestamps();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE venta_detalles ADD CONSTRAINT chk_vd_cantidad CHECK (cantidad > 0)');
            DB::statement('ALTER TABLE venta_detalles ADD CONSTRAINT chk_vd_subtotal CHECK (subtotal >= 0)');
            DB::statement('ALTER TABLE venta_detalles ADD CONSTRAINT chk_vd_total CHECK (total_linea >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
    }
};
