<?php

declare(strict_types=1);

use App\BusinessLogic\Restaurante\Delivery\ObtenerDepartamentosDelivery;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Restaurante\CrearPedidoPublicoController;
use App\Http\Requests\Restaurante\CrearPedidoPublicoRequest;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Facturacion\EmitirFacturaDesdeVenta;
use App\Interactors\Inventario\ConsumirStock;
use App\Interactors\Restaurante\Pedidos\CrearPedidoPublico;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
        'database.connections.sqlite.url' => null,
        'audit.enabled' => false,
        'cache.default' => 'array',
        'session.driver' => 'array',
    ]);
    DB::purge('sqlite');
    Http::preventStrayRequests();
});

test('un error interno de pedido se registra y no se expone al cliente', function (): void {
    $request = new CrearPedidoPublicoRequest;
    $request->setValidator(Validator::make(['items' => [['plato_id' => 1, 'cantidad' => 1]]], ['items' => 'array']));
    $interactor = app(CrearPedidoPublico::class);
    $error = new RuntimeException('SQL password=secreto-interno');
    $this->app->make(ExceptionHandler::class)->reportable(function (Throwable $reported) use ($error): void {
        expect($reported)->toBe($error);
    });
    DB::shouldReceive('transaction')->once()->andThrow($error);

    $response = (new CrearPedidoPublicoController)($request, $interactor);

    expect($response->getStatusCode())->toBe(500)
        ->and($response->getContent())->not->toContain('secreto-interno')
        ->and($response->getData(true)['success'])->toBeFalse();
});

test('el callback OAuth no registra parámetros sensibles ni expone errores', function (): void {
    Log::spy();
    Socialite::shouldReceive('driver')->with('google')->once()->andThrow(new RuntimeException('token=secreto-oauth'));
    $request = Request::create('/auth/google/callback', 'GET', ['code' => 'secreto-oauth', 'state' => 'estado-secreto']);
    $response = app(GoogleAuthController::class)->callback($request);

    expect($response->getTargetUrl())->toBe(route('login'));
    expect(session('error'))->not->toContain('secreto-oauth');
    Log::shouldHaveReceived('info')->with('[GoogleAuth] Callback recibido', ['has_code' => true, 'has_error' => false])->once();
    Log::shouldHaveReceived('error')->with('[GoogleAuth] Error en callback', ['exception_type' => RuntimeException::class])->once();
});

test('la redirección OAuth no expone el error del proveedor', function (): void {
    Log::spy();
    Socialite::shouldReceive('driver')->with('google')->once()->andThrow(new RuntimeException('client_secret=secreto'));
    $response = app(GoogleAuthController::class)->redireccionar(Request::create('/auth/google/redirect'));
    expect($response->getTargetUrl())->toBe(route('login'))
        ->and(session('error'))->not->toContain('client_secret');
});

test('delivery no reemplaza una lista vacía de zonas activas por tarifas alternativas', function (): void {
    Schema::create('restaurante_zonas_delivery', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo');
        $table->decimal('costo_envio');
        $table->integer('orden');
        $table->text('municipios');
    });
    expect(app(ObtenerDepartamentosDelivery::class)->ejecutar(true))->toBe([]);
});

test('delivery propaga errores de consulta que no sean tabla ausente en PostgreSQL', function (): void {
    expect(fn () => app(ObtenerDepartamentosDelivery::class)->ejecutar())->toThrow(QueryException::class);
});

function prepararStockSintetico(): void
{
    Schema::create('inv_lotes', function (Blueprint $table): void {
        $table->id();
        $table->integer('estado');
        $table->date('fecha_vencimiento')->nullable();
        $table->softDeletes();
    });
    Schema::create('inv_stock', function (Blueprint $table): void {
        $table->id();
        $table->integer('producto_id');
        $table->integer('producto_variante_id')->nullable();
        $table->integer('ubicacion_id');
        $table->integer('lote_id')->nullable();
        $table->decimal('cantidad', 12, 4);
        $table->timestamps();
    });
    DB::table('inv_stock')->insert(['id' => 1, 'producto_id' => 1, 'ubicacion_id' => 1, 'cantidad' => 10]);
}

test('revierte el descuento de stock si falla el registro del movimiento', function (): void {
    prepararStockSintetico();
    $movimientos = Mockery::mock(MovimientoStockRepositorioInterface::class);
    $movimientos->shouldReceive('registrar')->once()->andThrow(new RuntimeException('Fallo de movimiento'));
    app()->instance(MovimientoStockRepositorioInterface::class, $movimientos);

    expect(fn () => Stock::withoutEvents(fn () => app(ConsumirStock::class)->execute(1, 3, 1)))
        ->toThrow(RuntimeException::class, 'Fallo de movimiento');
    expect((float) DB::table('inv_stock')->value('cantidad'))->toBe(10.0);
});

test('un segundo consumo insuficiente no deja stock negativo', function (): void {
    prepararStockSintetico();
    $movimientos = Mockery::mock(MovimientoStockRepositorioInterface::class);
    $movimientos->shouldReceive('registrar')->once()->andReturn(new MovimientoStock);
    app()->instance(MovimientoStockRepositorioInterface::class, $movimientos);
    Stock::withoutEvents(fn () => app(ConsumirStock::class)->execute(1, 6, 1));
    expect(fn () => app(ConsumirStock::class)->execute(1, 6, 1))->toThrow(RuntimeException::class, 'Stock insuficiente');
    expect((float) DB::table('inv_stock')->value('cantidad'))->toBe(4.0);
});

test('un pago con una cuenta desactualizada valida el estado después de bloquear', function (): void {
    $anterior = new Cuenta;
    $anterior->setRawAttributes(['id' => 1, 'numero_cuenta' => 'PRUEBA', 'estado' => EstadoCuenta::ABIERTA->value]);
    $actual = clone $anterior;
    $actual->estado = EstadoCuenta::CERRADA;
    $cuentas = Mockery::mock(CuentaRepositorioInterface::class);
    $cuentas->shouldReceive('bloquear')->with(1)->once()->andReturn($actual);
    $cuentas->shouldNotReceive('crearPago');
    app()->instance(CuentaRepositorioInterface::class, $cuentas);

    expect(fn () => app(RegistrarPagoCuenta::class)->ejecutar($anterior, MetodoPago::EFECTIVO, 100))
        ->toThrow(DomainException::class, 'no acepta pagos');
});

function prepararMigracionStock(): object
{
    Schema::create('productos', fn (Blueprint $table) => $table->id());
    Schema::create('producto_variantes', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('producto_id')->constrained('productos');
    });
    Schema::create('inv_lotes', fn (Blueprint $table) => $table->id());
    DB::table('productos')->insert(['id' => 1]);
    DB::table('producto_variantes')->insert(['id' => 1, 'producto_id' => 1]);
    (require base_path('database/migrations/Inventario/2026_05_28_000010_create_stocks_table.php'))->up();

    return require base_path('database/migrations/Inventario/2026_09_20_231000_allow_simple_products_in_stocks_table.php');
}

test('la migración de stocks puede revertirse y aplicarse sin perder datos', function (): void {
    $migration = prepararMigracionStock();
    DB::table('stocks')->insert(['stockable_type' => 'prueba', 'stockable_id' => 1, 'producto_variante_id' => 1, 'cantidad_ideal' => 10, 'cantidad_actual' => 5]);
    $migration->up();
    $migration->down();
    expect(Schema::hasColumn('stocks', 'producto_id'))->toBeFalse();
    expect((float) DB::table('stocks')->value('cantidad_actual'))->toBe(5.0);
    expect(fn () => DB::table('stocks')->insert(['stockable_type' => 'prueba', 'stockable_id' => 1, 'producto_variante_id' => 1, 'cantidad_ideal' => 1, 'cantidad_actual' => 1]))->toThrow(QueryException::class);
    expect(fn () => DB::table('stocks')->insert(['stockable_type' => 'prueba', 'stockable_id' => 2, 'producto_variante_id' => null, 'cantidad_ideal' => 1, 'cantidad_actual' => 1]))->toThrow(QueryException::class);
    $migration->up();
    expect(DB::table('stocks')->value('producto_id'))->toBe(1);
});

test('la reversión rechaza stocks sin variante antes de modificar la estructura', function (): void {
    $migration = prepararMigracionStock();
    $migration->up();
    DB::table('stocks')->insert(['stockable_type' => 'prueba', 'stockable_id' => 1, 'producto_id' => 1, 'cantidad_ideal' => 10, 'cantidad_actual' => 5]);
    expect(fn () => $migration->down())->toThrow(RuntimeException::class, 'stocks sin variante');
    expect(Schema::hasColumn('stocks', 'producto_id'))->toBeTrue();
    expect(DB::table('stocks')->count())->toBe(1);
});

function prepararFacturaSintetica(): Venta
{
    foreach (['users', 'clientes', 'tasas_cambio', 'cuentas'] as $name) {
        Schema::create($name, function (Blueprint $table): void {
            $table->id();
            $table->softDeletes();
        });
    }
    foreach ([
        'Generales/2026_05_10_000001_create_monedas_table.php',
        'Cuentas/2026_07_27_000004_create_ventas_table.php',
        'Cuentas/2026_07_27_000005_create_venta_detalles_table.php',
        'Facturacion/2026_08_11_000001_create_factura_series_table.php',
        'Facturacion/2026_08_11_000002_create_factura_autorizaciones_dgi_table.php',
        'Facturacion/2026_08_11_000003_create_facturas_table.php',
        'Facturacion/2026_08_11_000004_create_factura_detalles_table.php',
        'Facturacion/2026_08_11_000010_create_factura_folios_table.php',
        'Facturacion/2026_08_16_000004_add_origen_columns_to_factura_detalles_table.php',
        'Facturacion/2026_08_16_000005_add_dgi_columns_to_facturas_table.php',
    ] as $file) {
        (require base_path('database/migrations/'.$file))->up();
    }
    DB::table('monedas')->insert(['id' => 1, 'codigo' => 'NIO', 'nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);
    DB::table('ventas')->insert(['id' => 1, 'numero_venta' => 'PRUEBA-1', 'moneda_id' => 1, 'subtotal' => 100, 'total' => 100]);
    DB::table('venta_detalles')->insert(['venta_id' => 1, 'concepto' => 'Consumo sintético', 'cantidad' => 1, 'precio_unitario' => 100, 'subtotal' => 100, 'total_linea' => 100]);
    DB::table('factura_series')->insert(['id' => 1, 'codigo' => 'PRUEBA', 'nombre' => 'Pruebas', 'siguiente_numero' => 1, 'activa' => true]);
    DB::table('factura_autorizaciones_dgi')->insert([
        'id' => 1, 'factura_serie_id' => 1, 'numero_autorizacion' => 'SINTETICA', 'ruc_emisor' => 'PRUEBA',
        'razon_social_emisor' => 'Prueba local', 'fecha_autorizacion' => now()->toDateString(),
        'rango_desde' => 1, 'rango_hasta' => 100, 'activa' => true,
    ]);

    return Venta::query()->findOrFail(1);
}

test('un fallo al crear el detalle de factura revierte factura folio y correlativo', function (): void {
    $venta = prepararFacturaSintetica();
    FacturaDetalle::creating(function (): never {
        throw new RuntimeException('Fallo sintético de detalle');
    });
    try {
        expect(fn () => app(EmitirFacturaDesdeVenta::class)->ejecutar($venta))
            ->toThrow(RuntimeException::class, 'Fallo sintético de detalle');
        expect(DB::table('facturas')->count())->toBe(0)
            ->and(DB::table('factura_folios')->count())->toBe(0)
            ->and(DB::table('factura_series')->value('siguiente_numero'))->toBe(1);
    } finally {
        FacturaDetalle::flushEventListeners();
    }
});

test('reintentar la emisión de una venta no duplica factura ni consume otro folio', function (): void {
    $venta = prepararFacturaSintetica();
    $emitir = app(EmitirFacturaDesdeVenta::class);
    $emitir->ejecutar($venta);
    expect(fn () => $emitir->ejecutar($venta))->toThrow(DomainException::class, 'ya tiene una factura emitida');
    expect(DB::table('facturas')->count())->toBe(1)
        ->and(DB::table('factura_folios')->count())->toBe(1)
        ->and(DB::table('factura_series')->value('siguiente_numero'))->toBe(2);
});

test('delivery registra el uso de configuración cuando falta la tabla en PostgreSQL', function (): void {
    Log::spy();
    $previous = new PDOException('Tabla ausente');
    $previous->errorInfo = ['42P01', 7, 'Tabla ausente'];
    $error = new QueryException('pgsql', 'select * from restaurante_zonas_delivery', [], $previous);
    DB::connection()->beforeExecuting(function () use ($error): never {
        throw $error;
    });
    config(['restaurante.delivery.departamentos_permitidos' => [['codigo' => 'TEST', 'nombre' => 'Prueba', 'activo' => true, 'costo_envio' => 10.0, 'municipios' => ['Prueba']]]]);

    expect(app(ObtenerDepartamentosDelivery::class)->ejecutar(true)[0]['codigo'])->toBe('TEST');
    Log::shouldHaveReceived('warning')->with('Delivery utiliza configuración porque falta la tabla de zonas.', ['sqlstate' => '42P01'])->once();
});

test('un fallo al registrar un pago revierte el pago y la actualización de totales', function (): void {
    Schema::create('cuentas_prueba', function (Blueprint $table): void {
        $table->id();
        $table->decimal('total');
    });
    Schema::create('pagos_prueba', function (Blueprint $table): void {
        $table->id();
        $table->decimal('monto');
    });
    DB::table('cuentas_prueba')->insert(['id' => 1, 'total' => 0]);
    $cuenta = new Cuenta;
    $cuenta->setRawAttributes(['id' => 1, 'numero_cuenta' => 'PRUEBA', 'estado' => EstadoCuenta::ABIERTA->value, 'moneda_id' => 1]);
    $cuentas = Mockery::mock(CuentaRepositorioInterface::class);
    $cuentas->shouldReceive('bloquear')->with(1)->once()->andReturn($cuenta);
    $cuentas->shouldReceive('refrescar')->once()->andReturn($cuenta);
    $cuentas->shouldReceive('subtotalDetallesActivos')->once()->andReturn(100.0);
    $cuentas->shouldReceive('sumaCargosActivos')->once()->andReturn(0.0);
    $cuentas->shouldReceive('cargosFacturacionObligatorios')->once()->andReturn(collect());
    $cuentas->shouldReceive('cargosFacturacionVigentesConCargoId')->once()->andReturn(collect());
    $cuentas->shouldReceive('cargosPorTipoActivos')->once()->andReturn(collect());
    $cuentas->shouldReceive('sumaPagosAplicados')->once()->andReturn(0.0);
    $cuentas->shouldReceive('actualizar')->once()->andReturnUsing(function (Cuenta $model, array $data): Cuenta {
        DB::table('cuentas_prueba')->where('id', 1)->update(['total' => $data['total']]);

        return $model->forceFill($data);
    });
    $cuentas->shouldReceive('crearPago')->once()->andReturnUsing(function (Cuenta $model, array $data): never {
        DB::table('pagos_prueba')->insert(['monto' => $data['monto']]);
        throw new RuntimeException('Fallo sintético de pago');
    });
    app()->instance(CuentaRepositorioInterface::class, $cuentas);

    expect(fn () => app(RegistrarPagoCuenta::class)->ejecutar($cuenta, MetodoPago::EFECTIVO, 50))
        ->toThrow(RuntimeException::class, 'Fallo sintético de pago');
    expect(DB::table('pagos_prueba')->count())->toBe(0)
        ->and((float) DB::table('cuentas_prueba')->value('total'))->toBe(0.0);
});
