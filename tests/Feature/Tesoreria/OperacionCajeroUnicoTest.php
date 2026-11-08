<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Interactors\Tesoreria\CobrarDesdeCaja;
use App\Interactors\Tesoreria\GestionarCaja;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\Tesoreria\MovimientoCaja;
use App\Repository\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config(['tesoreria.habilitado' => true]);

    $this->cajero = User::factory()->create(['is_admin' => true]);
    foreach (['ViewAny:Caja', 'Create:Caja', 'Abrir:Caja', 'Operar:Caja', 'Cerrar:Caja', 'Auditar:Caja', 'Create:Factura'] as $name) {
        $p = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $this->cajero->givePermissionTo($p);
    }

    $this->monedaNio = Moneda::firstOrCreate(
        ['codigo' => 'NIO'],
        ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]
    );
    $this->monedaUsd = Moneda::firstOrCreate(
        ['codigo' => 'USD'],
        ['nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => false]
    );

    $tipoRecep = Catalogo::firstOrCreate(
        ['codigo' => 'CAJA_FRONT_DESK'],
        ['nombre' => 'Recepción', 'catalogo_tipo_id' => 1, 'orden' => 1, 'estado' => 1]
    );
    $tipoRest = Catalogo::firstOrCreate(
        ['codigo' => 'CAJA_POS_RESTAURANTE'],
        ['nombre' => 'Restaurante', 'catalogo_tipo_id' => 1, 'orden' => 2, 'estado' => 1]
    );

    $ubiRecep = Ubicacion::firstOrCreate(
        ['nombre' => 'Recepción y Mostrador'],
        ['tipo' => 'sector', 'orden' => 1, 'estado' => 1]
    );
    $ubiRest = Ubicacion::firstOrCreate(
        ['nombre' => 'Restaurante Salón'],
        ['tipo' => 'sector', 'orden' => 2, 'estado' => 1]
    );

    $this->gestion = app(GestionarCaja::class);

    // Creamos dos cajas operativas: Recepción y Restaurante
    $this->cajaRecep = $this->gestion->crear(
        $this->cajero->id,
        'TEST-RECEP',
        'Caja Recepción Central',
        'Lobby',
        [$this->monedaNio->id, $this->monedaUsd->id],
        $ubiRecep->id,
        $tipoRecep->id
    );

    $this->cajaRest = $this->gestion->crear(
        $this->cajero->id,
        'TEST-REST',
        'Caja Restaurante',
        'Cocina/Salón',
        [$this->monedaNio->id, $this->monedaUsd->id],
        $ubiRest->id,
        $tipoRest->id
    );
});

test('un solo cajero abre una caja y centraliza cobros de recepcion, restaurante y servicios', function () {
    // 1. El único cajero abre solo la caja de recepción con fondo inicial de 1000 córdobas
    $sesionRecep = $this->gestion->abrir(
        $this->cajero->id,
        $this->cajaRecep->id,
        [['moneda_id' => $this->monedaNio->id, 'importe' => 1000.0], ['moneda_id' => $this->monedaUsd->id, 'importe' => 50.0]],
        'Fondo inicial entregado por administración para turno nocturno',
        Str::uuid()->toString()
    );

    expect($sesionRecep->estado)->toBe('abierta');
    expect($this->cajaRest->sesionActiva)->toBeNull();

    // 2. Se generan 3 cuentas de diferentes áreas del hotel
    // a) Cuenta de Estancia / Habitación (Recepción)
    $cuentaEstancia = Cuenta::create([
        'numero_cuenta' => 'CTA-ESTANCIA-'.Str::uuid(),
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'moneda_id' => $this->monedaNio->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $cuentaEstancia, concepto: 'Hospedaje noche suite', precioUnitario: 1500.0);
    $cuentaEstancia->refresh();

    // b) Cuenta de Restaurante (Consumo de comida/cena)
    $cuentaRestaurante = Cuenta::create([
        'numero_cuenta' => 'CTA-REST-'.Str::uuid(),
        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
        'moneda_id' => $this->monedaNio->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $cuentaRestaurante, concepto: 'Cena a la habitación (Room Service)', precioUnitario: 600.0);
    $cuentaRestaurante->refresh();

    // c) Cuenta de Servicios / Bar
    $cuentaBar = Cuenta::create([
        'numero_cuenta' => 'CTA-BAR-'.Str::uuid(),
        'tipo_cuenta' => TipoCuenta::SERVICIO,
        'moneda_id' => $this->monedaNio->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $cuentaBar, concepto: 'Bebidas y coctelería nocturna', precioUnitario: 350.0);
    $cuentaBar->refresh();

    // 3. El cajero único cobra cada una de las 3 cuentas desde su turno abierto en recepción
    $cobrar = app(CobrarDesdeCaja::class);

    // Cobro 1: Estancia (recibe 1500 NIO exactos)
    $cobro1 = $cobrar->ejecutar($this->cajero->id, (int) $cuentaEstancia->id, 1500.0, false, null, Str::uuid()->toString());
    expect($cobro1['pago_id'])->toBeGreaterThan(0);

    // Cobro 2: Restaurante (recibe billete de 1000 NIO, se calcula vuelto según total con impuestos)
    $totalRestaurante = (float) $cuentaRestaurante->refresh()->total;
    $vueltoEsperado = 1000.0 - $totalRestaurante;
    $cobro2 = $cobrar->ejecutar($this->cajero->id, (int) $cuentaRestaurante->id, 1000.0, false, null, Str::uuid()->toString());
    expect($cobro2['pago_id'])->toBeGreaterThan(0);

    // Cobro 3: Bar (recibe 350 NIO exactos)
    $cobro3 = $cobrar->ejecutar($this->cajero->id, (int) $cuentaBar->id, 350.0, false, null, Str::uuid()->toString());
    expect($cobro3['pago_id'])->toBeGreaterThan(0);

    // 4. Verificamos que todos los movimientos ingresaron a la sesión de recepción
    $movimientos = MovimientoCaja::where('sesion_id', $sesionRecep->id)->get();

    expect($movimientos->count())->toBe(4);

    $cobros = (float) $movimientos->where('tipo', 'cobro')->sum('importe');
    $vueltos = (float) $movimientos->where('tipo', 'vuelto')->sum('importe');

    expect($cobros)->toBe(2850.0);
    expect($vueltos)->toBe($vueltoEsperado);
    $ingresoNeto = $cobros - $vueltos;
    $netoEsperado = 1500.0 + $totalRestaurante + 350.0;
    expect($ingresoNeto)->toBe($netoEsperado);

    // 5. Verificamos que la otra caja (Caja Restaurante) no fue afectada en absoluto
    expect($this->cajaRest->sesionActiva)->toBeNull();
    expect(MovimientoCaja::where('sesion_id', $this->cajaRest->id)->count())->toBe(0);

    // 6. El cajero arquea y cierra su turno:
    $totalEsperadoNio = 1000.0 + $netoEsperado;
    $conteo = [
        ['moneda_id' => $this->monedaNio->id, 'importe' => $totalEsperadoNio],
        ['moneda_id' => $this->monedaUsd->id, 'importe' => 50.0],
    ];

    $sesionCerrada = $this->gestion->cerrar($this->cajero->id, $sesionRecep->id, $conteo);
    expect($sesionCerrada->estado)->toBe('cerrada');

    $saldos = $sesionCerrada->saldos;
    $saldoNio = $saldos->firstWhere('moneda_id', $this->monedaNio->id);
    expect((float) $saldoNio->apertura)->toBe(1000.0);
    expect((float) $saldoNio->esperado_cierre)->toBe($totalEsperadoNio);
    expect((float) $saldoNio->contado)->toBe($totalEsperadoNio);
    expect((float) $saldoNio->diferencia)->toBe(0.0); // Cuadre perfecto
});

test('si un cajero no tiene turno abierto el sistema bloquea el cobro en efectivo', function () {
    $cuenta = Cuenta::create([
        'numero_cuenta' => 'CTA-SIN-TURNO-'.Str::uuid(),
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'moneda_id' => $this->monedaNio->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $cuenta, concepto: 'Estancia demo', precioUnitario: 500.0);

    // Intentar cobrar en efectivo sin haber abierto ningún turno
    expect(fn () => app(RegistrarPagoCuenta::class)->ejecutar(
        cuenta: $cuenta,
        metodoPago: MetodoPago::EFECTIVO,
        monto: 500.0,
        usuarioId: $this->cajero->id
    ))->toThrow(DomainException::class, 'Abre tu turno de caja antes de recibir efectivo.');
});
