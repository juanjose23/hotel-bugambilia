<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Filament\Pages\Tesoreria\GestionCajas;
use App\Filament\Pages\Tesoreria\HistorialCajas;
use App\Filament\Pages\Tesoreria\MiTurnoCaja;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Interactors\Tesoreria\CobrarDesdeCaja;
use App\Interactors\Tesoreria\GestionarCaja;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\MovimientoCaja;
use App\Repository\Models\Tesoreria\SesionCaja;
use App\Repository\Models\User;
use App\Repository\Queries\Tesoreria\OperacionCajaQuery;
use Filament\Actions\Testing\TestAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config(['tesoreria.habilitado' => true]);
    $this->actor = User::factory()->create(['is_admin' => true]);
    $this->supervisor = User::factory()->create(['is_admin' => true]);
    foreach (['ViewAny:Caja', 'Create:Caja', 'Abrir:Caja', 'Operar:Caja', 'Egreso:Caja', 'Cerrar:Caja', 'CerrarDiferencia:Caja', 'Auditar:Caja', 'Create:Factura'] as $name) {
        $p = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $this->actor->givePermissionTo($p);
        $this->supervisor->givePermissionTo($p);
    }
    $this->moneda = Moneda::firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);
    $this->otra = Moneda::firstOrCreate(['codigo' => 'USD'], ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => false]);
    $this->gestion = app(GestionarCaja::class);
    $this->caja = $this->gestion->crear($this->actor->id, 'RECEP', 'Recepción', 'Lobby', [$this->moneda->id, $this->otra->id]);
    $this->abrir = function () {
        return $this->gestion->abrir($this->actor->id, $this->caja->id, [['moneda_id' => $this->moneda->id, 'importe' => 500.0], ['moneda_id' => $this->otra->id, 'importe' => 20.0]], 'Fondo inicial entregado por administración', Str::uuid()->toString());
    };
    $this->cuenta = Cuenta::create(['numero_cuenta' => 'DEMO-'.Str::uuid(), 'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO, 'moneda_id' => $this->moneda->id, 'estado' => EstadoCuenta::ABIERTA, 'abierta_at' => now()]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $this->cuenta, concepto: 'Consumo de demostración', precioUnitario: 100.0);
    $this->cuenta->refresh();
});

test('apertura exige cada moneda y evita dos turnos activos', function () {
    expect(fn () => $this->gestion->abrir($this->actor->id, $this->caja->id, [], 'Fondo recibido de gerencia', Str::uuid()->toString()))->toThrow(DomainException::class);
    ($this->abrir)();
    expect(fn () => ($this->abrir)())->toThrow(DomainException::class);
    expect(SesionCaja::count())->toBe(1);
});

test('repetir apertura con la misma clave devuelve el turno original', function () {
    $key = Str::uuid()->toString();
    $fondos = [['moneda_id' => $this->moneda->id, 'importe' => 500.0], ['moneda_id' => $this->otra->id, 'importe' => 20.0]];
    $a = $this->gestion->abrir($this->actor->id, $this->caja->id, $fondos, 'Entrega del administrador', $key);
    $b = $this->gestion->abrir($this->actor->id, $this->caja->id, $fondos, 'Entrega del administrador', $key);
    expect($a->id)->toBe($b->id)->and(SesionCaja::count())->toBe(1);
});

test('movimientos no duplican respaldo y rechazan un egreso sin fondos', function () {
    $s = ($this->abrir)();
    $key = Str::uuid()->toString();
    $m = $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', 25.0, 'Entrega extraordinaria', $key);
    $m2 = $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', 25.0, 'Entrega extraordinaria', $key);
    expect($m2->id)->toBe($m->id)->and(MovimientoCaja::count())->toBe(1);
    expect(fn () => $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', 30.0, 'Entrega extraordinaria', $key))->toThrow(DomainException::class);
    expect(fn () => $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'egreso', 526.0, 'Retiro autorizado a bóveda', Str::uuid()->toString()))->toThrow(DomainException::class);
});

test('otro cajero no opera el turno ajeno y un lector no abre caja', function () {
    $s = ($this->abrir)();
    expect(fn () => $this->gestion->movimiento($this->supervisor->id, $s->id, $this->moneda->id, 'ingreso', 1.0, 'Ingreso de prueba ajena', Str::uuid()->toString()))->toThrow(DomainException::class);
    $lector = User::factory()->create();
    expect(fn () => $this->gestion->abrir($lector->id, $this->caja->id, [], 'Entrega de prueba ajena', Str::uuid()->toString()))->toThrow(AuthorizationException::class);
});

test('cobro registra recibido y vuelto sin duplicar la liquidación', function () {
    $s = ($this->abrir)();
    $key = Str::uuid()->toString();
    $c = app(CobrarDesdeCaja::class);
    $a = $c->ejecutar($this->actor->id, $this->cuenta->id, 200.0, false, null, $key);
    $b = $c->ejecutar($this->actor->id, $this->cuenta->id, 200.0, false, null, $key);
    expect($a['pago_id'])->toBe($b['pago_id'])->and(PagoCuenta::count())->toBe(1)->and(Venta::count())->toBe(1);
    $p = PagoCuenta::firstOrFail();
    expect((float) $p->monto)->toBe((float) $this->cuenta->total)->and(MovimientoCaja::where('tipo', 'cobro')->sum('importe'))->toEqual(200.0)->and((float) MovimientoCaja::where('tipo', 'vuelto')->sum('importe'))->toBe(round(200.0 - (float) $p->monto, 2));
    $r = app(OperacionCajaQuery::class)->resumen($s);
    expect($r[0]['esperado'])->toBe(500.0 + (float) $p->monto);
});

test('abono repetido conserva un solo pago y rechaza reutilizar la clave', function () {
    ($this->abrir)();
    $key = Str::uuid()->toString();
    $c = app(CobrarDesdeCaja::class);
    $c->ejecutar($this->actor->id, $this->cuenta->id, 40.0, false, null, $key);
    $c->ejecutar($this->actor->id, $this->cuenta->id, 40.0, false, null, $key);
    expect(PagoCuenta::count())->toBe(1)->and((float) $this->cuenta->refresh()->total_pagado)->toBe(40.0);
    expect(fn () => $c->ejecutar($this->actor->id, $this->cuenta->id, 41.0, false, null, $key))->toThrow(DomainException::class);
});

test('sin turno el cobro revierte pago y saldo', function () {
    expect(fn () => app(CobrarDesdeCaja::class)->ejecutar($this->actor->id, $this->cuenta->id, 40.0, false, null, Str::uuid()->toString()))->toThrow(DomainException::class);
    expect(PagoCuenta::count())->toBe(0)->and((float) $this->cuenta->refresh()->total_pagado)->toBe(0.0)->and(MovimientoCaja::count())->toBe(0);
});

test('fallar emisión revierte pago movimientos venta y clave de cobro', function () {
    ($this->abrir)();
    expect(fn () => app(CobrarDesdeCaja::class)->ejecutar($this->actor->id, $this->cuenta->id, 200.0, true, 999999, Str::uuid()->toString()))->toThrow(DomainException::class);
    expect(PagoCuenta::count())->toBe(0)->and(MovimientoCaja::count())->toBe(0)->and(Venta::count())->toBe(0)->and(DB::table('tes_operaciones_cobro')->count())->toBe(0)->and((float) $this->cuenta->refresh()->total_pagado)->toBe(0.0);
});

test('efectivo en otra moneda se rechaza sin movimientos', function () {
    ($this->abrir)();
    expect(fn () => app(RegistrarPagoCuenta::class)->ejecutar($this->cuenta, MetodoPago::EFECTIVO, 20.0, monedaId: $this->otra->id, usuarioId: $this->actor->id))->toThrow(DomainException::class);
    expect(PagoCuenta::count())->toBe(0)->and(MovimientoCaja::count())->toBe(0);
});

test('tarjeta no se mezcla con el efectivo físico', function () {
    app(RegistrarPagoCuenta::class)->ejecutar($this->cuenta, MetodoPago::TARJETA_CREDITO, 40.0, monedaId: $this->moneda->id, usuarioId: $this->actor->id);
    expect(PagoCuenta::count())->toBe(1)->and(MovimientoCaja::count())->toBe(0);
});

test('cierre cuadrado conserva saldos y bloquea nuevos movimientos', function () {
    $s = ($this->abrir)();
    $c = $this->gestion->cerrar($this->actor->id, $s->id, [['moneda_id' => $this->moneda->id, 'importe' => 500.0], ['moneda_id' => $this->otra->id, 'importe' => 20.0]]);
    expect($c->estado)->toBe('cerrada')->and((float) $c->saldos()->firstOrFail()->diferencia)->toBe(0.0);
    expect(fn () => $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', 1.0, 'Ingreso después del cierre', Str::uuid()->toString()))->toThrow(DomainException::class);
    expect(($this->abrir)()->estado)->toBe('abierta');
});

test('faltante exige motivo revisión de otro usuario y conserva la diferencia', function () {
    $s = ($this->abrir)();
    $conteo = [['moneda_id' => $this->moneda->id, 'importe' => 490.0], ['moneda_id' => $this->otra->id, 'importe' => 20.0]];
    expect(fn () => $this->gestion->cerrar($this->actor->id, $s->id, $conteo))->toThrow(DomainException::class);
    expect($s->saldos()->firstOrFail()->contado)->toBeNull();
    $c = $this->gestion->cerrar($this->actor->id, $s->id, $conteo, 'Faltante detectado al contar efectivo');
    expect($c->estado)->toBe('pendiente_revision');
    expect(fn () => $this->gestion->aprobarCierre($this->actor->id, $s->id))->toThrow(DomainException::class);
    expect(fn () => ($this->abrir)())->toThrow(DomainException::class);
    $a = $this->gestion->aprobarCierre($this->supervisor->id, $s->id);
    expect($a->estado)->toBe('cerrada')->and($a->cerrada_por)->toBe($this->supervisor->id)->and((float) $a->saldos()->firstOrFail()->diferencia)->toBe(-10.0);
});

test('libro de caja impide edición y borrado en el modelo', function () {
    $s = ($this->abrir)();
    $m = $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', 1.0, 'Ingreso para probar historial', Str::uuid()->toString());
    expect(fn () => $m->update(['importe' => 2]))->toThrow(DomainException::class);
    expect(fn () => $m->delete())->toThrow(DomainException::class);
});

test('libro de caja también impide actualizaciones masivas en PostgreSQL', function () {
    $s = ($this->abrir)();
    $m = $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', 1.0, 'Ingreso para probar historial', Str::uuid()->toString());
    expect(fn () => DB::transaction(fn () => DB::table('tes_movimientos_caja')->where('id', $m->id)->update(['importe' => 2])))->toThrow(QueryException::class);
    expect((float) $m->refresh()->importe)->toBe(1.0);
});

test('pantallas de cajas turno e historial cargan y permiten abrir desde interfaz', function () {
    $this->actingAs($this->actor);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    Livewire::test(GestionCajas::class)->assertSuccessful()->assertSee('Recepción');
    Livewire::test(MiTurnoCaja::class)->assertSuccessful()->callAction('abrir', ['clave' => Str::uuid()->toString(), 'caja_id' => $this->caja->id, 'fondos' => [['moneda_id' => $this->moneda->id, 'importe' => 500], ['moneda_id' => $this->otra->id, 'importe' => 20]], 'origen' => 'Fondo entregado por administración'])->assertHasNoFormErrors();
    expect(SesionCaja::count())->toBe(1);
    Livewire::test(MiTurnoCaja::class)->assertSuccessful()->callAction('cobrar', ['clave' => Str::uuid()->toString(), 'cuenta_id' => $this->cuenta->id, 'recibido' => 200, 'factura' => false])->assertHasNoFormErrors();
    expect(PagoCuenta::count())->toBe(1);
    Livewire::test(HistorialCajas::class)->assertSuccessful();
});

test('cobro liquidado emite factura demo y consume un solo consecutivo', function () {
    ($this->abrir)();
    $serie = FacturaSerie::create(['codigo' => 'DEMO-TEST', 'nombre' => 'Serie de prueba', 'prefijo_fiscal' => 'DEMO', 'caja_id' => $this->caja->id, 'siguiente_numero' => 1, 'activa' => true]);
    FacturaAutorizacionDgi::create(['factura_serie_id' => $serie->id, 'numero_autorizacion' => 'DEMO-NO-VALIDA', 'ruc_emisor' => 'DEMO', 'razon_social_emisor' => 'Emisor ficticio', 'fecha_autorizacion' => today()->subDay(), 'vence_at' => today()->addMonth(), 'rango_desde' => 1, 'rango_hasta' => 99, 'activa' => true]);
    $key = Str::uuid()->toString();
    $c = app(CobrarDesdeCaja::class);
    $a = $c->ejecutar($this->actor->id, $this->cuenta->id, 200.0, true, $serie->id, $key);
    $b = $c->ejecutar($this->actor->id, $this->cuenta->id, 200.0, true, $serie->id, $key);
    expect($a['venta']->id)->toBe($b['venta']->id)->and($a['venta']->facturas()->count())->toBe(1)->and($serie->refresh()->siguiente_numero)->toBe(2);
});

test('lector no puede entrar al turno y caja apagada no muestra páginas', function () {
    $lector = User::factory()->create(['is_admin' => true]);
    $lector->givePermissionTo('ViewAny:Caja');
    $this->actingAs($lector);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    Livewire::test(MiTurnoCaja::class)->assertForbidden();
    $this->actingAs($this->actor);
    config(['tesoreria.habilitado' => false]);
    Livewire::test(GestionCajas::class)->assertForbidden();
});

test('importes inválidos no alteran el libro de caja', function (float $importe) {
    $s = ($this->abrir)();
    expect(fn () => $this->gestion->movimiento($this->actor->id, $s->id, $this->moneda->id, 'ingreso', $importe, 'Ingreso de prueba inválida', Str::uuid()->toString()))->toThrow(DomainException::class);
    expect(MovimientoCaja::count())->toBe(0);
})->with([-1.0, 0.0, 1.001, INF, NAN]);

test('supervisor revisa el arqueo desde gestión de cajas conservando el faltante', function () {
    $s = ($this->abrir)();
    $this->gestion->cerrar($this->actor->id, $s->id, [['moneda_id' => $this->moneda->id, 'importe' => 490.0], ['moneda_id' => $this->otra->id, 'importe' => 20.0]], 'Faltante de diez córdobas en el conteo');
    $this->actingAs($this->supervisor);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    Livewire::test(GestionCajas::class)->mountAction(TestAction::make('revisar')->table($this->caja))->call('forceRender')->assertSee('Faltante')->callMountedAction()->assertHasNoFormErrors();
    expect($s->refresh()->estado)->toBe('cerrada')->and($s->cerrada_por)->toBe($this->supervisor->id)->and((float) $s->saldos()->firstOrFail()->diferencia)->toBe(-10.0);
});

test('historial permite consultar el arqueo cerrado y su diferencia', function () {
    $s = ($this->abrir)();
    $this->gestion->cerrar($this->actor->id, $s->id, [['moneda_id' => $this->moneda->id, 'importe' => 490.0], ['moneda_id' => $this->otra->id, 'importe' => 20.0]], 'Faltante de diez córdobas en el conteo');
    $this->gestion->aprobarCierre($this->supervisor->id, $s->id);
    $this->actingAs($this->supervisor);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    Livewire::test(HistorialCajas::class)->mountAction(TestAction::make('ver')->table($s))->call('forceRender')->assertSee('Faltante')->assertSee('10,00');
});
