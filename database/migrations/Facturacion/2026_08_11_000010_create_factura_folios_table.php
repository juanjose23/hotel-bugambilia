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
        Schema::create('factura_folios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('factura_serie_id')->constrained('factura_series')->restrictOnDelete();
            $table->foreignId('factura_autorizacion_dgi_id')->constrained('factura_autorizaciones_dgi')->restrictOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            $table->unsignedBigInteger('numero_correlativo');
            $table->string('numero', 50);
            $table->unsignedTinyInteger('estado')->default(1)->index();
            $table->timestamp('reservado_at')->nullable();
            $table->timestamp('emitido_at')->nullable();
            $table->timestamp('anulado_at')->nullable();
            $table->timestamp('fallido_at')->nullable();
            $table->foreignId('reservado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo', 500)->nullable();
            $table->jsonb('meta_datos')->nullable();
            $table->timestamps();

            $table->unique(['factura_serie_id', 'numero_correlativo'], 'uq_folio_serie_correlativo');
            $table->unique(['factura_serie_id', 'numero'], 'uq_folio_serie_numero');
            $table->index(['factura_autorizacion_dgi_id', 'estado'], 'idx_folio_autorizacion_estado');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE factura_folios ADD CONSTRAINT chk_ff_estado CHECK (estado IN (1, 2, 3, 4))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_folios');
    }
};
