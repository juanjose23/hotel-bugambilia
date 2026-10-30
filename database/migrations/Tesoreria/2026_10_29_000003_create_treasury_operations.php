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
        Schema::create('tes_fondos', function (Blueprint $t): void {
            $t->id();
            $t->string('codigo', 30)->unique();
            $t->string('nombre', 100);
            $t->string('tipo', 20);
            $t->foreignId('moneda_id')->constrained('monedas')->restrictOnDelete();
            $t->foreignId('responsable_id')->constrained('users')->restrictOnDelete();
            $t->string('banco', 100)->nullable();
            $t->string('identificacion', 100)->nullable();
            $t->decimal('limite_vale', 14, 2)->default(0);
            $t->boolean('activo')->default(true);
            $t->timestamps();
        });
        Schema::create('tes_operaciones', function (Blueprint $t): void {
            $t->id();
            $t->uuid('clave')->unique();
            $t->string('huella', 64);
            $t->string('tipo', 30);
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->json('resultado')->nullable();
            $t->timestamps();
        });
        Schema::create('tes_movimientos_fondo', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('fondo_id')->constrained('tes_fondos')->restrictOnDelete();
            $t->foreignId('operacion_id')->constrained('tes_operaciones')->restrictOnDelete();
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->decimal('importe', 14, 2);
            $t->string('concepto', 1000);
            $t->string('referencia', 150);
            $t->timestamps();
            $t->unique(['fondo_id', 'operacion_id']);
            $t->index(['fondo_id', 'id']);
        });
        Schema::create('tes_extractos', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('fondo_id')->constrained('tes_fondos')->restrictOnDelete();
            $t->date('fecha');
            $t->string('referencia', 150);
            $t->decimal('importe', 14, 2);
            $t->string('soporte');
            $t->string('sha256', 64);
            $t->foreignId('registrado_por')->constrained('users')->restrictOnDelete();
            $t->timestamps();
            $t->unique(['fondo_id', 'referencia']);
        });
        Schema::create('tes_conciliaciones', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('extracto_id')->unique()->constrained('tes_extractos')->restrictOnDelete();
            $t->foreignId('movimiento_id')->unique()->constrained('tes_movimientos_fondo')->restrictOnDelete();
            $t->foreignId('pago_transaccion_id')->nullable()->unique()->constrained('pago_transacciones')->restrictOnDelete();
            $t->decimal('comision', 14, 2)->default(0);
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->timestamps();
        });
        Schema::create('tes_vales', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('fondo_id')->constrained('tes_fondos')->restrictOnDelete();
            $t->foreignId('solicitante_id')->constrained('users')->restrictOnDelete();
            $t->string('beneficiario', 150);
            $t->string('concepto', 1000);
            $t->decimal('importe', 14, 2);
            $t->date('vence_at');
            $t->string('estado', 20)->default('solicitado');
            $t->foreignId('aprobado_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->foreignId('liquidado_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->decimal('gastado', 14, 2)->nullable();
            $t->decimal('devuelto', 14, 2)->nullable();
            $t->string('referencia', 150)->nullable();
            $t->string('soporte')->nullable();
            $t->text('resolucion')->nullable();
            $t->timestamps();
        });
        Schema::create('tes_comprobantes', function (Blueprint $t): void {
            $t->id();
            $t->uuid('codigo')->unique();
            $t->foreignId('cuenta_id')->constrained('cuentas')->restrictOnDelete();
            $t->foreignId('fondo_id')->constrained('tes_fondos')->restrictOnDelete();
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->decimal('importe', 14, 2);
            $t->date('fecha');
            $t->string('referencia', 150);
            $t->string('estado', 20)->default('pendiente');
            $t->text('decision')->nullable();
            $t->foreignId('revisado_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->foreignId('pago_cuenta_id')->nullable()->unique()->constrained('pagos_cuenta')->restrictOnDelete();
            $t->foreignId('extracto_id')->nullable()->unique()->constrained('tes_extractos')->restrictOnDelete();
            $t->timestamp('revisado_at')->nullable();
            $t->timestamps();
            $t->unique(['fondo_id', 'referencia']);
            $t->index(['estado', 'id']);
        });
        Schema::create('tes_decisiones_comprobante', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('comprobante_id')->constrained('tes_comprobantes')->restrictOnDelete();
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->string('decision', 20);
            $t->text('motivo');
            $t->timestamps();
        });
        Schema::create('tes_evidencias', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('comprobante_id')->constrained('tes_comprobantes')->restrictOnDelete();
            $t->string('ruta');
            $t->string('sha256', 64);
            $t->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $t->timestamps();
        });
        Schema::create('tes_devoluciones', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('pago_cuenta_id')->constrained('pagos_cuenta')->restrictOnDelete();
            $t->foreignId('sesion_id')->constrained('tes_sesiones_caja')->restrictOnDelete();
            $t->foreignId('solicitante_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('aprobado_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->decimal('importe', 14, 2);
            $t->string('motivo', 1000);
            $t->string('estado', 20)->default('solicitada');
            $t->text('decision')->nullable();
            $t->timestamps();
            $t->index(['pago_cuenta_id', 'estado']);
        });
        Schema::create('tes_entregas', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('sesion_id')->constrained('tes_sesiones_caja')->restrictOnDelete();
            $t->foreignId('fondo_id')->constrained('tes_fondos')->restrictOnDelete();
            $t->foreignId('entregado_por')->constrained('users')->restrictOnDelete();
            $t->foreignId('recibido_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->decimal('importe', 14, 2);
            $t->string('referencia', 150);
            $t->string('estado', 20)->default('en_transito');
            $t->timestamps();
        });
        Schema::create('tes_abastecimientos', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('sesion_id')->constrained('tes_sesiones_caja')->restrictOnDelete();
            $t->foreignId('fondo_id')->constrained('tes_fondos')->restrictOnDelete();
            $t->foreignId('solicitante_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('aprobado_por')->nullable()->constrained('users')->restrictOnDelete();
            $t->decimal('importe', 14, 2);
            $t->string('referencia', 150);
            $t->string('estado', 20)->default('pendiente');
            $t->text('decision')->nullable();
            $t->timestamps();
        });
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared("CREATE OR REPLACE FUNCTION tes_proteger_fondo() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN IF EXISTS (SELECT 1 FROM tes_movimientos_fondo WHERE fondo_id=OLD.id) AND (TG_OP='DELETE' OR NEW.moneda_id IS DISTINCT FROM OLD.moneda_id OR NEW.tipo IS DISTINCT FROM OLD.tipo OR NEW.codigo IS DISTINCT FROM OLD.codigo OR NEW.banco IS DISTINCT FROM OLD.banco OR NEW.identificacion IS DISTINCT FROM OLD.identificacion) THEN RAISE EXCEPTION 'La identidad y moneda del fondo con historia se conservan'; END IF; IF TG_OP='DELETE' THEN RETURN OLD; END IF; RETURN NEW; END; \$\$");
            DB::unprepared('CREATE TRIGGER tes_fondo_protegido BEFORE UPDATE OR DELETE ON tes_fondos FOR EACH ROW EXECUTE FUNCTION tes_proteger_fondo()');

            DB::statement("ALTER TABLE tes_fondos ADD CONSTRAINT tes_tipo_fondo CHECK (tipo IN ('banco','caja_chica','boveda') AND limite_vale>=0)");
            foreach (['tes_movimientos_fondo', 'tes_extractos'] as $table) {
                DB::statement("ALTER TABLE $table ADD CHECK (importe <> 0)");
                DB::unprepared("CREATE TRIGGER {$table}_inmutable BEFORE UPDATE OR DELETE ON $table FOR EACH ROW EXECUTE FUNCTION tes_proteger_movimiento()");
            }
            foreach (['tes_evidencias', 'tes_conciliaciones', 'tes_decisiones_comprobante'] as $table) {
                DB::unprepared("CREATE TRIGGER {$table}_inmutable BEFORE UPDATE OR DELETE ON $table FOR EACH ROW EXECUTE FUNCTION tes_proteger_movimiento()");
            }
            DB::statement("ALTER TABLE tes_vales ADD CHECK (importe>0 AND (gastado IS NULL OR gastado>=0) AND (devuelto IS NULL OR devuelto>=0) AND estado IN ('solicitado','entregado','liquidado','rechazado'))");
            DB::statement("ALTER TABLE tes_comprobantes ADD CHECK (importe>0 AND estado IN ('pendiente','aprobado','rechazado','aclaracion'))");
            DB::statement("ALTER TABLE tes_devoluciones ADD CHECK (importe>0 AND estado IN ('solicitada','aprobada','rechazada'))");
            DB::statement("ALTER TABLE tes_entregas ADD CHECK (importe>0 AND estado IN ('en_transito','recibida'))");
        }
    }

    public function down(): void
    {
        if (DB::table('tes_fondos')->exists()) {
            throw new RuntimeException('Preserve los registros de tesorería antes de revertir.');
        }
        foreach (['tes_abastecimientos', 'tes_entregas', 'tes_devoluciones', 'tes_decisiones_comprobante', 'tes_evidencias', 'tes_comprobantes', 'tes_vales', 'tes_conciliaciones', 'tes_extractos', 'tes_movimientos_fondo', 'tes_operaciones', 'tes_fondos'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
