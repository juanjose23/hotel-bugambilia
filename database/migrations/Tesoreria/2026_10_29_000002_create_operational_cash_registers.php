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
        Schema::create('tes_cajas', function (Blueprint $t): void {
            $t->id();
            $t->string('codigo', 30)->unique();
            $t->string('nombre', 100);
            $t->string('ubicacion', 150);
            $t->boolean('activa')->default(true);
            $t->foreignId('creada_por')->constrained('users')->restrictOnDelete();
            $t->timestamps();
        });
        Schema::create('tes_caja_monedas', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('caja_id')->constrained('tes_cajas')->restrictOnDelete();
            $t->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $t->unique(['caja_id', 'moneda_id']);
        });
        Schema::create('tes_sesiones_caja', function (Blueprint $t): void {
            $t->id();
            $t->uuid('codigo')->unique();
            $t->foreignId('caja_id')->constrained('tes_cajas')->restrictOnDelete();
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->string('estado', 25)->default('abierta');
            $t->text('origen_fondo');
            $t->timestamp('abierta_at');
            $t->timestamp('contada_at')->nullable();
            $t->timestamp('cerrada_at')->nullable();
            $t->foreignId('cerrada_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->text('motivo_diferencia')->nullable();
            $t->timestamps();
        });
        Schema::create('tes_saldos_sesion', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('sesion_id')->constrained('tes_sesiones_caja')->restrictOnDelete();
            $t->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $t->decimal('apertura', 14, 2);
            $t->decimal('esperado_cierre', 14, 2)->nullable();
            $t->decimal('contado', 14, 2)->nullable();
            $t->decimal('diferencia', 14, 2)->nullable();
            $t->timestamps();
            $t->unique(['sesion_id', 'moneda_id']);
        });
        Schema::create('tes_movimientos_caja', function (Blueprint $t): void {
            $t->id();
            $t->uuid('clave_operacion')->unique();
            $t->foreignId('sesion_id')->constrained('tes_sesiones_caja')->restrictOnDelete();
            $t->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->string('tipo', 15);
            $t->decimal('importe', 14, 2);
            $t->string('concepto', 1000);
            $t->string('referencia', 150)->nullable();
            $t->foreignId('pago_cuenta_id')->nullable()->constrained('pagos_cuenta')->restrictOnDelete();
            $t->timestamps();
            $t->unique(['pago_cuenta_id', 'tipo']);
            $t->index(['sesion_id', 'moneda_id', 'id']);
        });
        Schema::create('tes_operaciones_cobro', function (Blueprint $t): void {
            $t->id();
            $t->uuid('clave')->unique();
            $t->foreignId('cuenta_id')->constrained('cuentas')->restrictOnDelete();
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('pago_cuenta_id')->unique()->constrained('pagos_cuenta')->restrictOnDelete();
            $t->foreignId('venta_id')->nullable()->constrained('ventas')->restrictOnDelete();
            $t->string('huella_peticion', 64);
            $t->timestamps();
        });
        if (in_array(DB::getDriverName(), ['pgsql', 'sqlite'], true)) {
            DB::statement("CREATE UNIQUE INDEX tes_caja_activa_unica ON tes_sesiones_caja(caja_id) WHERE estado IN ('abierta','pendiente_revision')");
            DB::statement("CREATE UNIQUE INDEX tes_cajero_activo_unico ON tes_sesiones_caja(usuario_id) WHERE estado IN ('abierta','pendiente_revision')");
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tes_sesiones_caja ADD CONSTRAINT tes_estado_sesion_valido CHECK (estado IN ('abierta','pendiente_revision','cerrada'))");
            DB::statement('ALTER TABLE tes_saldos_sesion ADD CONSTRAINT tes_apertura_valida CHECK (apertura >= 0 AND (contado IS NULL OR contado >= 0))');
            DB::statement("ALTER TABLE tes_movimientos_caja ADD CONSTRAINT tes_movimiento_valido CHECK (importe > 0 AND tipo IN ('ingreso','egreso','cobro','vuelto'))");
            DB::unprepared("CREATE OR REPLACE FUNCTION tes_proteger_movimiento() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'Los movimientos confirmados de caja son inmutables'; END; $$");
            DB::unprepared('CREATE TRIGGER tes_movimiento_inmutable BEFORE UPDATE OR DELETE ON tes_movimientos_caja FOR EACH ROW EXECUTE FUNCTION tes_proteger_movimiento()');
        }
    }

    public function down(): void
    {
        if (DB::table('tes_sesiones_caja')->exists() || DB::table('tes_cajas')->exists()) {
            throw new RuntimeException('Hay cajas o sesiones registradas; preserve la auditoría antes de revertir.');
        }
        Schema::dropIfExists('tes_operaciones_cobro');
        Schema::dropIfExists('tes_movimientos_caja');
        Schema::dropIfExists('tes_saldos_sesion');
        Schema::dropIfExists('tes_sesiones_caja');
        Schema::dropIfExists('tes_caja_monedas');
        Schema::dropIfExists('tes_cajas');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION IF EXISTS tes_proteger_movimiento()');
        }
    }
};
