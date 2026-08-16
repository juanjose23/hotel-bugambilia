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
        Schema::create('ventas', function (Blueprint $table): void {
            $table->id();
            $table->string('numero_venta', 50)->unique();
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('moneda_id')->nullable()->constrained('monedas')->nullOnDelete();

            // Totales congelados (fotografía histórica)
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento_total', 12, 2)->default(0);
            $table->decimal('impuesto_total', 12, 2)->default(0);
            $table->decimal('servicio_total', 12, 2)->default(0);
            $table->decimal('propina_total', 12, 2)->default(0);
            $table->decimal('recargo_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->unsignedTinyInteger('estado')->default(1)->comment('EstadoVenta: 1=Emitida, 2=Anulada, 3=NotaCredito');
            $table->jsonb('datos_fiscales')->nullable()->comment('RTN, CAI, rango, fecha_emision, etc.');

            $table->foreignId('creada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anulada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulada_en')->nullable();
            $table->timestamps();

            $table->index('cuenta_id');
            $table->index('cliente_id');
            $table->index('estado');
            $table->index('created_at');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_estado CHECK (estado IN (1, 2, 3))');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_subtotal CHECK (subtotal >= 0)');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_descuento CHECK (descuento_total >= 0)');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_impuesto CHECK (impuesto_total >= 0)');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_servicio CHECK (servicio_total >= 0)');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_propina CHECK (propina_total >= 0)');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_recargo CHECK (recargo_total >= 0)');
            DB::statement('ALTER TABLE ventas ADD CONSTRAINT chk_ventas_total CHECK (total >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
