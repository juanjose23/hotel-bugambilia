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
        if (! DB::table('monedas')->exists()) {
            DB::table('monedas')->insert([
                'codigo' => 'NIO',
                'nombre' => 'Córdoba Nicaragüense',
                'simbolo' => 'C$',
                'es_predeterminada' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $defaultMonedaId = DB::table('monedas')->where('es_predeterminada', true)->value('id')
            ?? DB::table('monedas')->value('id');

        // ── 1. cuenta_detalles ─────────────────────────────────────
        Schema::table('cuenta_detalles', function (Blueprint $table) use ($defaultMonedaId): void {
            $table->unsignedBigInteger('moneda_id')->after('cuenta_id')->default($defaultMonedaId);
        });

        $this->backfillMoneda('cuenta_detalles', 'cuenta_id', 'cuentas');
        $this->setMonedaNotNull('cuenta_detalles', 'fk_cuenta_detalles_moneda', 'idx_cuenta_detalles_moneda_id');

        // ── 2. cuenta_cargos ──────────────────────────────────────
        Schema::table('cuenta_cargos', function (Blueprint $table) use ($defaultMonedaId): void {
            $table->unsignedBigInteger('moneda_id')->after('cuenta_id')->default($defaultMonedaId);
        });

        $this->backfillMoneda('cuenta_cargos', 'cuenta_id', 'cuentas');
        $this->setMonedaNotNull('cuenta_cargos', 'fk_cuenta_cargos_moneda', 'idx_cuenta_cargos_moneda_id');

        // ── 3. venta_detalles ─────────────────────────────────────
        Schema::table('venta_detalles', function (Blueprint $table) use ($defaultMonedaId): void {
            $table->unsignedBigInteger('moneda_id')->after('venta_id')->default($defaultMonedaId);
        });

        $this->backfillMoneda('venta_detalles', 'venta_id', 'ventas');
        $this->setMonedaNotNull('venta_detalles', 'fk_venta_detalles_moneda', 'idx_venta_detalles_moneda_id');
    }

    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table): void {
            $table->dropForeign('fk_venta_detalles_moneda');
            $table->dropIndex('idx_venta_detalles_moneda_id');
            $table->dropColumn('moneda_id');
        });

        Schema::table('cuenta_cargos', function (Blueprint $table): void {
            $table->dropForeign('fk_cuenta_cargos_moneda');
            $table->dropIndex('idx_cuenta_cargos_moneda_id');
            $table->dropColumn('moneda_id');
        });

        Schema::table('cuenta_detalles', function (Blueprint $table): void {
            $table->dropForeign('fk_cuenta_detalles_moneda');
            $table->dropIndex('idx_cuenta_detalles_moneda_id');
            $table->dropColumn('moneda_id');
        });
    }

    /**
     * Sincroniza el moneda_id de las filas existentes con su encabezado.
     */
    private function backfillMoneda(string $tabla, string $fkColumna, string $tablaFuente): void
    {
        DB::table($tabla)
            ->select("$tabla.id as detalle_id", "$tablaFuente.moneda_id as moneda_fuente")
            ->join($tablaFuente, "$tablaFuente.id", '=', "$tabla.$fkColumna")
            ->whereColumn("$tabla.moneda_id", '!=', "$tablaFuente.moneda_id")
            ->orderBy("$tabla.id")
            ->chunk(500, function ($filas) use ($tabla): void {
                foreach ($filas as $fila) {
                    DB::table($tabla)->where('id', $fila->detalle_id)->update([
                        'moneda_id' => $fila->moneda_fuente,
                    ]);
                }
            });
    }

    /**
     * Aplica NOT NULL, foreign key e índice solo en motores que lo soportan nativamente.
     */
    private function setMonedaNotNull(string $tabla, string $fkNombre, string $idxNombre): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE $tabla ALTER COLUMN moneda_id SET NOT NULL");

        Schema::table($tabla, function (Blueprint $table) use ($fkNombre, $idxNombre): void {
            $table->foreign('moneda_id', $fkNombre)->references('id')->on('monedas')->restrictOnDelete();
            $table->index('moneda_id', $idxNombre);
        });
    }
};
