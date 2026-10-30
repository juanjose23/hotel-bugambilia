<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Cuentas\PagoCuentaRegistrado;
use App\Events\Reservas\ReservaConfirmada;
use App\Filament\Pages\Tesoreria\CajaChica;
use App\Filament\Pages\Tesoreria\ComprobantesTransferencia;
use App\Filament\Pages\Tesoreria\ConciliacionBancaria as ConciliacionPagina;
use App\Filament\Pages\Tesoreria\DevolucionesCaja;
use App\Filament\Pages\Tesoreria\EntregasCaja;
use App\Filament\Pages\Tesoreria\FondosTesoreria;
use App\Filament\Pages\Tesoreria\MovimientosFondos;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Interactors\Facturacion\ConfirmarPagoPasarela;
use App\Interactors\Facturacion\RegistrarTransaccionPasarela;
use App\Interactors\Tesoreria\AbastecerTurnoCaja;
use App\Interactors\Tesoreria\ArchivosTesoreria;
use App\Interactors\Tesoreria\GestionarCaja;
use App\Interactors\Tesoreria\GestionarCajaChica;
use App\Interactors\Tesoreria\GestionarComprobantes;
use App\Interactors\Tesoreria\GestionarConciliacion;
use App\Interactors\Tesoreria\GestionarDevoluciones;
use App\Interactors\Tesoreria\GestionarEntregas;
use App\Interactors\Tesoreria\GestionarFondos;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Facturacion\PasarelaPago;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Tesoreria\AbastecimientoCaja;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\Tesoreria\ComprobanteTransferencia;
use App\Repository\Models\Tesoreria\ConciliacionBancaria;
use App\Repository\Models\Tesoreria\DevolucionCaja;
use App\Repository\Models\Tesoreria\EntregaCaja;
use App\Repository\Models\Tesoreria\EvidenciaTransferencia;
use App\Repository\Models\Tesoreria\ExtractoBancario;
use App\Repository\Models\Tesoreria\FondoTesoreria;
use App\Repository\Models\Tesoreria\MovimientoFondo;
use App\Repository\Models\Tesoreria\ValeCajaChica;
use App\Repository\Models\User;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use App\Repository\Persistencia\Tesoreria\TesoreriaRepositorio;
use Database\Seeders\Tesoreria\CajaPermisosSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config(['tesoreria.habilitado' => true]);
    Storage::fake('private');
    Http::preventStrayRequests();
    Event::fake([PagoCuentaRegistrado::class, ReservaConfirmada::class]);
    $this->seed(CajaPermisosSeeder::class);
    $this->supervisor = User::factory()->create(['is_admin' => true]);
    $this->operador = User::factory()->create(['is_admin' => true]);
    $this->cliente = User::factory()->create(['email_verified_at' => now()]);
    $this->supervisor->syncPermissions(Permission::all());
    $this->operador->syncPermissions(['Operar:CajaChica', 'Entregar:Caja', 'Solicitar:Devolucion', 'Operar:Caja', 'Abrir:Caja', 'Cerrar:Caja']);
    $this->nio = Moneda::firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);
    $this->repo = app(TesoreriaRepositorio::class);
    $this->fondos = app(GestionarFondos::class);
    $this->fondos->crear($this->supervisor->id, 'BANCO-QA', 'Banco sintético', 'banco', $this->nio->id, $this->supervisor->id, 2000.0, 'Saldo según extracto de prueba', 0.0, Str::uuid()->toString(), 'Banco ficticio', 'DEMO-001');
    $this->banco = FondoTesoreria::where('codigo', 'BANCO-QA')->firstOrFail();
    $this->fondos->crear($this->supervisor->id, 'CHICA-QA', 'Caja chica sintética', 'caja_chica', $this->nio->id, $this->operador->id, 0.0, 'Fondo que será provisto por traslado', 250.0, Str::uuid()->toString());
    $this->chica = FondoTesoreria::where('codigo', 'CHICA-QA')->firstOrFail();
    $this->reserva = Reserva::create(['codigo_reserva' => 'DEMO-'.Str::uuid(), 'nombre_cliente' => 'Cliente sintético', 'email_cliente' => $this->cliente->email, 'tipo_reserva' => TipoReserva::HABITACION, 'moneda_id' => $this->nio->id, 'fecha_check_in' => today()->addDays(10), 'fecha_check_out' => today()->addDays(12), 'estado' => EstadoReserva::PENDIENTE]);
    $this->cuenta = Cuenta::create(['numero_cuenta' => 'DEMO-'.Str::uuid(), 'reserva_id' => $this->reserva->id, 'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO, 'moneda_id' => $this->nio->id, 'estado' => EstadoCuenta::ABIERTA, 'abierta_at' => now()]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $this->cuenta, concepto: 'Consumo sintético', precioUnitario: 100.0);
    $this->cuenta->refresh();
    $this->soporte = function (): string {
        return app(ArchivosTesoreria::class)->guardar(UploadedFile::fake()->image('extracto.png'));
    };
    $this->extracto = function (float $importe = 40.0, string $ref = 'DEMO-REF'): ExtractoBancario {
        app(GestionarConciliacion::class)->registrar($this->supervisor->id, $this->banco->id, today()->toDateString(), $ref, $importe, ($this->soporte)(), Str::uuid()->toString());

        return ExtractoBancario::where('referencia', $ref)->firstOrFail();
    };
    $this->presentar = function (string $ref = 'DEMO-REF'): ComprobanteTransferencia {
        app(GestionarComprobantes::class)->presentar($this->cliente->id, $this->cuenta->id, $this->banco->id, 40.0, today()->toDateString(), $ref, UploadedFile::fake()->image('voucher.png'), Str::uuid()->toString());

        return ComprobanteTransferencia::where('referencia', $ref)->firstOrFail();
    };
    $this->abrir = function () {
        $g = app(GestionarCaja::class);
        $c = $g->crear($this->supervisor->id, 'RECEP-QA', 'Caja sintética', 'Recepción', [$this->nio->id]);

        return $g->abrir($this->operador->id, $c->id, [['moneda_id' => $this->nio->id, 'importe' => 500.0]], 'Fondo sintético entregado por supervisor', Str::uuid()->toString());
    };
});

test('reposición conserva dinero y repetir operación no duplica ni genera otro gasto', function () {
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        $this->fondos->transferir($this->supervisor->id, $this->banco->id, $this->chica->id, 300.0, 'Reposición autorizada de caja chica', $key);
    }
    expect($this->repo->saldoCentavos($this->banco->id))->toBe(170000)->and($this->repo->saldoCentavos($this->chica->id))->toBe(30000)->and(MovimientoFondo::count())->toBe(3);
    expect(fn () => $this->fondos->transferir($this->supervisor->id, $this->banco->id, $this->chica->id, 301.0, 'Reposición autorizada de caja chica', $key))->toThrow(DomainException::class);
});

test('traslado no admite moneda diferente ni sobregiro y revierte ambos lados', function () {
    expect(fn () => $this->fondos->transferir($this->supervisor->id, $this->banco->id, $this->chica->id, 2001.0, 'Traslado de prueba sin saldo', Str::uuid()->toString()))->toThrow(DomainException::class);
    $usd = Moneda::firstOrCreate(['codigo' => 'USD'], ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => false]);
    $this->chica->update(['moneda_id' => $usd->id]);
    expect(fn () => $this->fondos->transferir($this->supervisor->id, $this->banco->id, $this->chica->id, 10.0, 'Traslado de prueba sin cambio', Str::uuid()->toString()))->toThrow(DomainException::class);
    expect(MovimientoFondo::count())->toBe(1);
});

test('vale exige otro aprobador liquida gasto y devuelve remanente sin duplicarlo', function () {
    $this->fondos->transferir($this->supervisor->id, $this->banco->id, $this->chica->id, 300.0, 'Financiamiento para gastos menores', Str::uuid()->toString());
    $g = app(GestionarCajaChica::class);
    $g->solicitar($this->operador->id, $this->chica->id, 'Encargado sintético', 100.0, 'Compra de insumos de limpieza', today()->addDay()->toDateString(), Str::uuid()->toString());
    $v = ValeCajaChica::firstOrFail();
    expect($this->repo->saldoCentavos($this->chica->id))->toBe(30000);
    $this->operador->givePermissionTo('Aprobar:CajaChica');
    expect(fn () => $g->resolver($this->operador->id, $v->id, true, 'Autorización propia no permitida', Str::uuid()->toString()))->toThrow(DomainException::class);
    $g->resolver($this->supervisor->id, $v->id, true, 'Autorizado por supervisor independiente', Str::uuid()->toString());
    expect($this->repo->saldoCentavos($this->chica->id))->toBe(20000);
    $key = Str::uuid()->toString();
    $soporte = ($this->soporte)();
    foreach ([1, 2] as $_) {
        $g->liquidar($this->supervisor->id, $v->id, 75.0, 'FACTURA-DEMO-01', $soporte, $key);
    }
    expect($v->refresh()->estado)->toBe('liquidado')->and((float) $v->gastado)->toBe(75.0)->and((float) $v->devuelto)->toBe(25.0)->and($this->repo->saldoCentavos($this->chica->id))->toBe(22500);
});

test('vale rechazado y vale sin fondos no retiran dinero', function () {
    $g = app(GestionarCajaChica::class);
    $g->solicitar($this->operador->id, $this->chica->id, 'Encargado sintético', 50.0, 'Solicitud sin financiamiento previo', today()->addDay()->toDateString(), Str::uuid()->toString());
    $v = ValeCajaChica::firstOrFail();
    expect(fn () => $g->resolver($this->supervisor->id, $v->id, true, 'Autorizar un vale sin fondo', Str::uuid()->toString()))->toThrow(DomainException::class);
    $g->resolver($this->supervisor->id, $v->id, false, 'Se rechaza porque no hay fondos', Str::uuid()->toString());
    expect($v->refresh()->estado)->toBe('rechazado')->and($this->repo->saldoCentavos($this->chica->id))->toBe(0);
});

test('subir imagen no acredita dinero y repetir envío conserva una evidencia', function () {
    $g = app(GestionarComprobantes::class);
    $archivo = UploadedFile::fake()->image('voucher.png');
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        $g->presentar($this->cliente->id, $this->cuenta->id, $this->banco->id, 40.0, today()->toDateString(), 'DEMO-REF', $archivo, $key);
    }
    expect(ComprobanteTransferencia::count())->toBe(1)->and(EvidenciaTransferencia::count())->toBe(1)->and(Storage::disk('private')->allFiles('tesoreria'))->toHaveCount(1)->and(PagoCuenta::count())->toBe(0)->and((float) $this->cuenta->refresh()->total_pagado)->toBe(0.0)->and($this->reserva->refresh()->estado)->toBe(EstadoReserva::PENDIENTE);
});

test('aprobar transferencia requiere extracto coincidente y genera un solo pago y crédito', function () {
    $v = ($this->presentar)();
    $g = app(GestionarComprobantes::class);
    expect(fn () => $g->revisar($this->supervisor->id, $v->id, 'aprobado', 'Revisión contra banco requerida', Str::uuid()->toString()))->toThrow(DomainException::class);
    $e = ($this->extracto)();
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        $g->revisar($this->supervisor->id, $v->id, 'aprobado', 'Referencia y monto verificados contra banco', $key, $e->id);
    }
    expect(PagoCuenta::count())->toBe(1)->and(ConciliacionBancaria::count())->toBe(1)->and((float) $this->cuenta->refresh()->total_pagado)->toBe(40.0)->and((float) $this->reserva->refresh()->total_pagado)->toBe(40.0)->and($this->repo->saldoCentavos($this->banco->id))->toBe(204000)->and($v->refresh()->estado)->toBe('aprobado');
});

test('no se reutiliza referencia ni extracto y una diferencia no aplica dinero', function () {
    $v = ($this->presentar)();
    expect(fn () => ($this->presentar)())->toThrow(DomainException::class);
    $e = ($this->extracto)(39.0);
    expect(fn () => app(GestionarComprobantes::class)->revisar($this->supervisor->id, $v->id, 'aprobado', 'Revisión de diferencia de importes', Str::uuid()->toString(), $e->id))->toThrow(DomainException::class);
    expect(PagoCuenta::count())->toBe(0)->and(ConciliacionBancaria::count())->toBe(0);
});

test('rechazo y aclaración conservan evidencia e historial sin aplicar dinero', function () {
    $v = ($this->presentar)();
    $g = app(GestionarComprobantes::class);
    $g->revisar($this->supervisor->id, $v->id, 'aclaracion', 'La imagen está borrosa y requiere reemplazo', Str::uuid()->toString());
    $g->presentar($this->cliente->id, $this->cuenta->id, $this->banco->id, 40.0, today()->toDateString(), 'DEMO-REF', UploadedFile::fake()->image('nueva.png'), Str::uuid()->toString(), $v->id);
    $g->revisar($this->supervisor->id, $v->id, 'rechazado', 'El banco no registra la transferencia reportada', Str::uuid()->toString());
    expect(EvidenciaTransferencia::count())->toBe(2)->and(DB::table('tes_decisiones_comprobante')->count())->toBe(2)->and($v->refresh()->estado)->toBe('rechazado')->and(PagoCuenta::count())->toBe(0);
});

test('cliente ajeno no lee cuenta ni evidencia y archivos públicos no son aceptados', function () {
    $v = ($this->presentar)();
    $otro = User::factory()->create();
    $this->actingAs($otro)->get(route('portal.comprobantes', ['cuenta' => $this->cuenta->id]))->assertForbidden();
    $this->get(route('tesoreria.archivo', ['tipo' => 'comprobante', 'id' => $v->id]))->assertForbidden();
    $this->actingAs($this->cliente)->get(route('tesoreria.archivo', ['tipo' => 'comprobante', 'id' => $v->id]))->assertOk();
    expect(fn () => app(ArchivosTesoreria::class)->huella('../public/voucher.png'))->toThrow(DomainException::class);
});

test('portal permite enviar comprobante y rechaza archivos ejecutables', function () {
    $this->actingAs($this->cliente)->post(route('portal.comprobantes.store', ['cuenta' => $this->cuenta->id]), ['banco_id' => $this->banco->id, 'importe' => 40, 'fecha' => today()->toDateString(), 'referencia' => 'DEMO-PORTAL', 'archivo' => UploadedFile::fake()->image('voucher.png'), 'clave' => Str::uuid()->toString()])->assertRedirect()->assertSessionHasNoErrors();
    $this->post(route('portal.comprobantes.store', ['cuenta' => $this->cuenta->id]), ['banco_id' => $this->banco->id, 'importe' => 40, 'fecha' => today()->toDateString(), 'referencia' => 'DEMO-MALO', 'archivo' => UploadedFile::fake()->create('mal.php', 1, 'text/x-php'), 'clave' => Str::uuid()->toString()])->assertSessionHasErrors('archivo');
    expect(PagoCuenta::count())->toBe(0);
});

test('reserva cancelada o pagada por otra vía impide aprobación y preserva comprobante pendiente', function () {
    $v = ($this->presentar)();
    $e = ($this->extracto)();
    $this->reserva->update(['estado' => EstadoReserva::CANCELADA]);
    expect(fn () => app(GestionarComprobantes::class)->revisar($this->supervisor->id, $v->id, 'aprobado', 'Reserva cancelada necesita revisión manual', Str::uuid()->toString(), $e->id))->toThrow(DomainException::class);
    expect($v->refresh()->estado)->toBe('pendiente')->and(PagoCuenta::count())->toBe(0);
});

test('captura de pasarela queda pendiente hasta liquidar neto y comisión contra extracto', function () {
    $pasarela = PasarelaPago::create(['codigo' => 'QA', 'nombre' => 'Pasarela sintética', 'activa' => true]);
    $t = app(RegistrarTransaccionPasarela::class)->ejecutar($pasarela, 100.0, $this->nio, 'REF-PASARELA', cuenta: $this->cuenta);
    $t = app(ConfirmarPagoPasarela::class)->ejecutar($t, 'CAPTURA-DEMO');
    expect($t->conciliacion->estado)->toBe(EstadoConciliacionPago::Pendiente)->and($t->conciliacion->conciliada_at)->toBeNull()->and($this->repo->saldoCentavos($this->banco->id))->toBe(200000);
    $e = ($this->extracto)(97.0, 'NETO-TARJETA');
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        app(GestionarConciliacion::class)->liquidarPasarela($this->supervisor->id, $t->id, $e->id, 3.0, $key);
    }
    expect($t->refresh()->conciliacion->estado)->toBe(EstadoConciliacionPago::Conciliada)->and((float) $t->conciliacion->monto_recibido)->toBe(97.0)->and((float) ConciliacionBancaria::firstOrFail()->comision)->toBe(3.0)->and(PagoCuenta::count())->toBe(1)->and($this->repo->saldoCentavos($this->banco->id))->toBe(209700);
});

test('conciliar no modifica saldos ni permite usar dos veces la partida', function () {
    $this->fondos->movimiento($this->supervisor->id, $this->banco->id, 40.0, 'Ingreso registrado en el libro', 'DEMO-REF', Str::uuid()->toString());
    $m = MovimientoFondo::latest('id')->firstOrFail();
    $e = ($this->extracto)();
    $g = app(GestionarConciliacion::class);
    $g->conciliar($this->supervisor->id, $e->id, $m->id, Str::uuid()->toString());
    expect(fn () => $g->conciliar($this->supervisor->id, $e->id, $m->id, Str::uuid()->toString()))->toThrow(DomainException::class);
    expect($this->repo->saldoCentavos($this->banco->id))->toBe(204000);
});

test('entrega sale de caja y queda en tránsito hasta recepción independiente', function () {
    $s = ($this->abrir)();
    $g = app(GestionarEntregas::class);
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        $g->entregar($this->operador->id, $this->banco->id, 100.0, 'DEPOSITO-DEMO', $key);
    }$e = EntregaCaja::firstOrFail();
    $cr = app(CajaRepositorio::class);
    expect($cr->esperado($s, $cr->saldos($s)->first()))->toBe(400.0)->and($this->repo->saldoCentavos($this->banco->id))->toBe(200000);
    expect(fn () => $g->recibir($this->supervisor->id, $e->id, Str::uuid()->toString()))->toThrow(DomainException::class);
    $x = ($this->extracto)(100.0, 'DEPOSITO-DEMO');
    $g->recibir($this->supervisor->id, $e->id, Str::uuid()->toString(), $x->id);
    expect($e->refresh()->estado)->toBe('recibida')->and($this->repo->saldoCentavos($this->banco->id))->toBe(210000)->and(ConciliacionBancaria::count())->toBe(1);
});

test('devolución no borra pago descuenta efectivo y no supera monto original', function () {
    $s = ($this->abrir)();
    $p = app(RegistrarPagoCuenta::class)->ejecutar($this->cuenta, MetodoPago::EFECTIVO, 50.0, monedaId: $this->nio->id, usuarioId: $this->operador->id);
    $g = app(GestionarDevoluciones::class);
    $g->solicitar($this->operador->id, $p->id, 20.0, 'Devolución parcial autorizada del consumo', Str::uuid()->toString());
    $d = DevolucionCaja::firstOrFail();
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        $g->resolver($this->supervisor->id, $d->id, true, 'Autorización independiente de devolución', $key);
    }
    $cr = app(CajaRepositorio::class);
    expect($cr->esperado($s, $cr->saldos($s)->first()))->toBe(530.0)->and($p->refresh()->estado)->toBe(EstadoPago::APLICADO)->and((float) $p->monto)->toBe(50.0)->and($d->refresh()->estado)->toBe('aprobada');
    expect(fn () => $g->solicitar($this->operador->id, $p->id, 31.0, 'Devolución que excede monto original', Str::uuid()->toString()))->toThrow(DomainException::class);
});

test('libro bancario y extractos son inmutables en base de datos', function () {
    $e = ($this->extracto)();
    $m = MovimientoFondo::firstOrFail();
    expect(fn () => DB::transaction(fn () => DB::table('tes_movimientos_fondo')->where('id', $m->id)->delete()))->toThrow(QueryException::class);
    expect(fn () => DB::transaction(fn () => DB::table('tes_extractos')->where('id', $e->id)->update(['importe' => 999])))->toThrow(QueryException::class);
});

test('pantallas de tesorería cargan y un lector no ejecuta operaciones', function () {
    $this->actingAs($this->supervisor);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    foreach ([FondosTesoreria::class, MovimientosFondos::class, CajaChica::class, ConciliacionPagina::class, ComprobantesTransferencia::class, DevolucionesCaja::class, EntregasCaja::class] as $page) {
        Livewire::test($page)->assertSuccessful();
    }
    $lector = User::factory()->create(['is_admin' => true]);
    $lector->givePermissionTo('ViewAny:Fondos');
    $this->actingAs($lector);
    Livewire::test(FondosTesoreria::class)->assertSuccessful()->assertActionHidden('crear')->assertActionHidden('transferir');
    Livewire::test(ConciliacionPagina::class)->assertForbidden();
    expect(fn () => $this->fondos->transferir($lector->id, $this->banco->id, $this->chica->id, 1.0, 'Intento de traslado no autorizado', Str::uuid()->toString()))->toThrow(AuthorizationException::class);
    $this->get(route('tesoreria.auditoria'))->assertForbidden();
});

test('transferencia liquidada permite emitir factura y un fallo fiscal revierte toda aprobación', function () {
    $this->supervisor->givePermissionTo(Permission::firstOrCreate(['name' => 'Create:Factura', 'guard_name' => 'web']));
    $g = app(GestionarComprobantes::class);
    $total = (float) $this->cuenta->total;
    $g->presentar($this->cliente->id, $this->cuenta->id, $this->banco->id, $total, today()->toDateString(), 'DEMO-TOTAL', UploadedFile::fake()->image('total.png'), Str::uuid()->toString());
    $v = ComprobanteTransferencia::firstOrFail();
    $e = ($this->extracto)($total, 'DEMO-TOTAL');
    expect(fn () => $g->revisar($this->supervisor->id, $v->id, 'aprobado', 'Pago completo revisado para factura', Str::uuid()->toString(), $e->id, true, 999999))->toThrow(DomainException::class);
    expect(PagoCuenta::count())->toBe(0)->and(ConciliacionBancaria::count())->toBe(0)->and($v->refresh()->estado)->toBe('pendiente')->and($this->repo->saldoCentavos($this->banco->id))->toBe(200000);
    $s = FacturaSerie::create(['codigo' => 'QA-BANCO', 'nombre' => 'Serie ficticia', 'prefijo_fiscal' => 'DEMO', 'caja_id' => Caja::firstOrCreate(['codigo' => 'QA'], ['nombre' => 'Caja sintética bancaria', 'ubicacion' => 'Recepción de pruebas', 'creada_por' => $this->supervisor->id])->id, 'siguiente_numero' => 1, 'activa' => true]);
    FacturaAutorizacionDgi::create(['factura_serie_id' => $s->id, 'numero_autorizacion' => 'QA-NO-FISCAL', 'ruc_emisor' => 'DEMO', 'razon_social_emisor' => 'Emisor sintético', 'fecha_autorizacion' => today()->subDay(), 'vence_at' => today()->addMonth(), 'rango_desde' => 1, 'rango_hasta' => 99, 'activa' => true]);
    $g->revisar($this->supervisor->id, $v->id, 'aprobado', 'Pago completo revisado para factura', Str::uuid()->toString(), $e->id, true, $s->id);
    expect(Factura::count())->toBe(1)->and($s->refresh()->siguiente_numero)->toBe(2)->and($this->cuenta->refresh()->estado)->toBe(EstadoCuenta::CERRADA);
});

test('auditor consulta comprobantes y exporta movimientos sin poder aprobarlos', function () {
    $v = ($this->presentar)();
    $auditor = User::factory()->create(['is_admin' => true]);
    $auditor->syncPermissions(['Auditar:Fondos', 'ViewAny:Fondos']);
    $this->actingAs($auditor);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    Livewire::test(ComprobantesTransferencia::class)->assertSuccessful()->assertActionHidden(TestAction::make('revisar')->table($v))->mountAction(TestAction::make('evidencias')->table($v))->call('forceRender')->assertSee('Ver evidencia');
    $this->get(route('tesoreria.archivo', ['tipo' => 'comprobante', 'id' => $v->id]))->assertOk();
    $this->get(route('tesoreria.auditoria'))->assertOk();
    expect(fn () => app(GestionarComprobantes::class)->revisar($auditor->id, $v->id, 'aprobado', 'Intento no autorizado de aprobación', Str::uuid()->toString()))->toThrow(AuthorizationException::class);
});

test('desde caja chica y conciliación se ejecutan los formularios del servidor', function () {
    $this->actingAs($this->operador);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    Livewire::test(CajaChica::class)->callAction('solicitar', ['clave' => Str::uuid()->toString(), 'fondo_id' => $this->chica->id, 'beneficiario' => 'Personal de limpieza', 'importe' => 100, 'vence' => today()->addDay()->toDateString(), 'concepto' => 'Vale para insumos de limpieza'])->assertHasNoFormErrors();
    expect(ValeCajaChica::count())->toBe(1);
    $this->actingAs($this->supervisor);
    Livewire::test(FondosTesoreria::class)->callAction('transferir', ['clave' => Str::uuid()->toString(), 'origen' => $this->banco->id, 'destino' => $this->chica->id, 'importe' => 200, 'concepto' => 'Reposición desde cuenta bancaria'])->assertHasNoFormErrors();
    expect($this->repo->saldoCentavos($this->chica->id))->toBe(20000);
});

test('fondo con movimientos no permite cambiar moneda histórica', function () {
    $usd = Moneda::firstOrCreate(['codigo' => 'USD'], ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => false]);
    expect(fn () => DB::transaction(fn () => DB::table('tes_fondos')->where('id', $this->banco->id)->update(['moneda_id' => $usd->id])))->toThrow(QueryException::class);
    expect($this->banco->refresh()->moneda_id)->toBe($this->nio->id);
});

test('vale sin gasto puede liquidarse devolviendo todo el dinero', function () {
    $this->fondos->transferir($this->supervisor->id, $this->banco->id, $this->chica->id, 100.0, 'Fondo de prueba para vale no utilizado', Str::uuid()->toString());
    $g = app(GestionarCajaChica::class);
    $g->solicitar($this->operador->id, $this->chica->id, 'Beneficiario sintético', 50.0, 'Vale que no llegó a utilizarse', today()->addDay()->toDateString(), Str::uuid()->toString());
    $v = ValeCajaChica::firstOrFail();
    $g->resolver($this->supervisor->id, $v->id, true, 'Entrega para compra de insumos', Str::uuid()->toString());
    $g->liquidar($this->supervisor->id, $v->id, 0.0, 'DEVOLUCION-TOTAL', ($this->soporte)(), Str::uuid()->toString());
    expect((float) $v->refresh()->devuelto)->toBe(50.0)->and($this->repo->saldoCentavos($this->chica->id))->toBe(10000);
});

test('fondo de turno descuenta su origen y bloquea cierre mientras la solicitud está pendiente', function () {
    $this->operador->givePermissionTo('Solicitar:FondoTurno');
    $s = ($this->abrir)();
    $g = app(AbastecerTurnoCaja::class);
    $g->solicitar($this->operador->id, $this->banco->id, 100.0, 'RETIRO-TURNO', Str::uuid()->toString());
    $v = AbastecimientoCaja::firstOrFail();
    expect(fn () => app(GestionarCaja::class)->cerrar($this->operador->id, $s->id, [['moneda_id' => $this->nio->id, 'importe' => 500.0]]))->toThrow(DomainException::class);
    $e = ($this->extracto)(-100.0, 'RETIRO-TURNO');
    $key = Str::uuid()->toString();
    foreach ([1, 2] as $_) {
        $g->resolver($this->supervisor->id, $v->id, true, 'Fondo contado y entregado al cajero', $key, $e->id);
    }
    $cr = app(CajaRepositorio::class);
    expect($cr->esperado($s, $cr->saldos($s)->first()))->toBe(600.0)->and($this->repo->saldoCentavos($this->banco->id))->toBe(190000)->and($v->refresh()->estado)->toBe('aprobado')->and(ConciliacionBancaria::count())->toBe(1);
});
