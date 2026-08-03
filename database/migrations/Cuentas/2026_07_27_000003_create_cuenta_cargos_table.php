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
        Schema::create('cuenta_cargos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas')->restrictOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos_facturacion')->nullOnDelete();

            // Fotografía histórica del catálogo al momento de aplicar
            $table->unsignedTinyInteger('tipo')->comment('TipoCargo');
            $table->string('codigo', 20)->comment('Código del cargo aplicado');
            $table->string('nombre', 100)->comment('Nombre del cargo aplicado');
            $table->unsignedTinyInteger('modo_calculo')->comment('ModoCargo');
            $table->decimal('valor', 8, 4)->comment('Valor (% o monto fijo) utilizado');
            $table->unsignedTinyInteger('base_calculo')->comment('BaseCalculo');
            $table->decimal('base_monto', 12, 2)->comment('Monto base sobre el cual se calculó');
            $table->decimal('monto', 12, 2)->comment('Resultado del cargo aplicado');

            // Origen polimórfico (qué originó este cargo)
            $table->nullableMorphs('origen');

            $table->foreignId('aplicado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('estado')->default(1)->comment('1=Activo, 0=Anulado');
            $table->text('observaciones')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
            $table->index(['cuenta_id', 'estado']);
            $table->index(['cuenta_id', 'cargo_id']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cuenta_cargos ADD CONSTRAINT chk_cuenta_cargos_tipo CHECK (tipo BETWEEN 1 AND 6)');
            DB::statement('ALTER TABLE cuenta_cargos ADD CONSTRAINT chk_cuenta_cargos_modo CHECK (modo_calculo BETWEEN 1 AND 3)');
            DB::statement('ALTER TABLE cuenta_cargos ADD CONSTRAINT chk_cuenta_cargos_base CHECK (base_calculo BETWEEN 1 AND 4)');
            DB::statement('ALTER TABLE cuenta_cargos ADD CONSTRAINT chk_cuenta_cargos_estado CHECK (estado IN (0, 1))');
            DB::statement('ALTER TABLE cuenta_cargos ADD CONSTRAINT chk_cuenta_cargos_monto CHECK (monto >= 0)');

            // Índice único parcial: un mismo cargo no puede aplicarse dos veces al mismo origen en la misma cuenta
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_cuenta_cargo_origen ON cuenta_cargos (cuenta_id, cargo_id, origen_type, origen_id) WHERE estado = 1 AND origen_type IS NOT NULL AND cargo_id IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_cargos');
    }
};
