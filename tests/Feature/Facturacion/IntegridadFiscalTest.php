<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\EstadoFolioFactura;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Resources\Facturacion\FacturaResource\Pages\ViewFactura;
use App\Interactors\Cuentas\Cobros\ProcesarCobroCuenta;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\CerrarCuentaYGenerarVenta;
use App\Interactors\Facturacion\AnularFacturaFiscal;
use App\Interactors\Facturacion\EmitirFacturaDesdeVenta;
use App\Interactors\Facturacion\ImprimirFactura;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\Support\Barcode\Support\HotelInfo;
use Database\Seeders\Configuracion\CatalogoSeeder;
use Database\Seeders\Configuracion\CatalogoTipoSeeder;
use Database\Seeders\Configuracion\MonedaSeeder;
use Database\Seeders\Configuracion\PaisSeeder;
use Database\Seeders\Configuracion\UbicacionSeeder;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\TasaCambioSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/../../Support/escenarios-integridad-fiscal.php';

beforeEach(function () {
    Http::preventStrayRequests();
    Storage::fake('local');
    Storage::fake('public');
});

test('FI-01 pago cierre y emision fiscal conservan cuenta venta detalles y folio', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    $factura = $venta->facturas->firstOrFail();
    expect($f['cuenta']->refresh()->estado)->toBe(EstadoCuenta::CERRADA)->and((float) $f['cuenta']->saldo)->toBe(0.0)->and((float) $factura->total)->toBe((float) $venta->total)->and($factura->detalles()->count())->toBe(1)->and($factura->folios()->firstOrFail()->estado)->toBe(EstadoFolioFactura::Emitido)->and($f['serie']->refresh()->siguiente_numero)->toBe(2);
});

test('FI-02 repetir la emision no duplica factura ni consume otro consecutivo', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    expect(fn () => app(EmitirFacturaDesdeVenta::class)->ejecutar($venta, $f['serie']->id))->toThrow(DomainException::class);
    expect(Factura::count())->toBe(1)->and(FacturaFolio::count())->toBe(1)->and($f['serie']->refresh()->siguiente_numero)->toBe(2);
});

test('FI-03 una autorizacion vencida revierte cierre y venta conservando el pago anterior', function () {
    $f = escenarioIntegridadFiscal();
    $f['autorizacion']->update(['vence_at' => today()->subDay()]);
    app(RegistrarPagoCuenta::class)->ejecutar($f['cuenta'], MetodoPago::EFECTIVO, 100.0);
    expect(fn () => app(CerrarCuentaYGenerarVenta::class)->ejecutar($f['cuenta']->refresh(), $f['usuario']->id, ['emitir_factura' => true, 'factura_serie_id' => $f['serie']->id]))->toThrow(DomainException::class);
    expect(Venta::count())->toBe(0)->and(Factura::count())->toBe(0)->and($f['cuenta']->refresh()->estado)->toBe(EstadoCuenta::ABIERTA)->and((float) $f['cuenta']->total_pagado)->toBe(100.0);
});

test('FI-04 el agotamiento del rango no consume un folio fuera de autorizacion', function () {
    $f = escenarioIntegridadFiscal(1);
    emitirIntegridadFiscal($f);
    $g = escenarioIntegridadFiscal();
    app(RegistrarPagoCuenta::class)->ejecutar($g['cuenta'], MetodoPago::EFECTIVO, 100.0);
    expect(fn () => app(CerrarCuentaYGenerarVenta::class)->ejecutar($g['cuenta']->refresh(), $g['usuario']->id, ['emitir_factura' => true, 'factura_serie_id' => $f['serie']->id]))->toThrow(DomainException::class);
    expect(FacturaFolio::where('factura_serie_id', $f['serie']->id)->count())->toBe(1)->and($f['serie']->refresh()->siguiente_numero)->toBe(2);
});

test('FI-05 anular conserva documento folio y pago sin reutilizar consecutivo', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    $factura = $venta->facturas->firstOrFail();
    app(AnularFacturaFiscal::class)->ejecutar($factura, 'Error de datos del receptor - prueba', $f['usuario']->id);
    app(AnularFacturaFiscal::class)->ejecutar($factura->refresh(), 'Solicitud repetida de auditoría', $f['usuario']->id);
    expect($factura->refresh()->estado)->toBe(EstadoFactura::Anulada)->and($factura->folios()->firstOrFail()->estado)->toBe(EstadoFolioFactura::Anulado)->and($f['serie']->refresh()->siguiente_numero)->toBe(2)->and(PagoCuenta::firstOrFail()->estado)->toBe(EstadoPago::APLICADO);
});

test('FI-06 reimprimir produce PDF y no reserva otro folio', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    $factura = $venta->facturas->firstOrFail();
    $r = app(ImprimirFactura::class)->ejecutarPdf($factura);
    ob_start();
    try {
        $r->sendContent();
        $pdf = ob_get_contents();
    } finally {
        ob_end_clean();
    }
    expect($pdf)->toStartWith('%PDF-')->and(FacturaFolio::count())->toBe(1)->and($f['serie']->refresh()->siguiente_numero)->toBe(2);
});

test('FI-07 elegir factura en cobro debe emitir el documento fiscal', function () {
    $f = escenarioIntegridadFiscal();
    $r = app(ProcesarCobroCuenta::class)->ejecutar($f['cuenta'], $f['usuario']->id, ['forma_pago' => MetodoPago::EFECTIVO->value, 'moneda_pago_id' => $f['moneda']->id, 'monto' => (float) $f['cuenta']->total, 'tipo_comprobante' => 'factura', 'factura_serie_id' => $f['serie']->id, 'ruc_factura' => 'DEMO', 'razon_social_factura' => 'Cliente de demostración']);
    expect($r['cerrada'])->toBeTrue()->and($r['venta']->facturas()->count())->toBe(1);
});

test('FI-08 moneda distinta sin tasa debe impedir emitir en equivalencia 1 a 1', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    $otra = Moneda::firstOrCreate(['codigo' => 'ZZZ'], ['nombre' => 'Moneda sintética sin tasa', 'simbolo' => 'Z', 'es_predeterminada' => false]);
    $venta2 = $venta->replicate();
    $venta2->numero_venta = 'QA-OTRA-'.bin2hex(random_bytes(4));
    $venta2->moneda_id = $otra->id;
    $venta2->save();
    expect(fn () => app(EmitirFacturaDesdeVenta::class)->ejecutar($venta2, $f['serie']->id))->toThrow(DomainException::class);
});

test('FI-10 autorizacion con fecha futura debe impedir emitir hoy', function () {
    $f = escenarioIntegridadFiscal();
    $f['autorizacion']->update(['fecha_autorizacion' => today()->addDays(10), 'vence_at' => today()->addDays(40)]);
    expect(fn () => emitirIntegridadFiscal($f))->toThrow(DomainException::class);
});

test('FI-11 un usuario solo de lectura no puede anular desde la ficha', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    $factura = $venta->facturas->firstOrFail();
    $this->actingAs($f['usuario']);
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));
    foreach (['ViewAny:Factura', 'View:Factura'] as $nombre) {
        $f['usuario']->givePermissionTo(Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']));
    }
    Livewire\Livewire::test(ViewFactura::class, ['record' => $factura->id])->assertSuccessful()->assertActionHidden('anular_factura')->call('mountAction', 'anular_factura')->assertSet('mountedActions', []);
    expect($factura->refresh()->estado)->toBe(EstadoFactura::Emitida);
});

test('FI-12 el motivo de anulacion debe cumplir los diez caracteres del requisito', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    expect(fn () => app(AnularFacturaFiscal::class)->ejecutar($venta->facturas->firstOrFail(), 'x', $f['usuario']->id))->toThrow(DomainException::class);
});

test('FI-13 transferencia publica sin aprobacion no debe aplicar dinero ni confirmar reserva', function () {
    Event::fake();
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        HabitacionSeeder::class,
    ]);
    $habitacion = Habitacion::firstOrFail();
    $this->postJson(route('reservas.crear'), [
        'nombre_cliente' => 'Cliente Transferencia DEMO', 'telefono_cliente' => '+505 8888 0000', 'email_cliente' => 'transferencia-auditoria@demo.local',
        'tipo_reserva' => TipoReserva::HABITACION->value, 'habitacion_id' => $habitacion->id,
        'fecha_check_in' => today()->addDays(45)->toDateString(), 'fecha_check_out' => today()->addDays(47)->toDateString(),
        'adultos' => 2, 'tipo_pago_reserva' => TipoPagoReserva::ABONO_50->value, 'canal_pago_reserva' => 'transferencia',
    ])->assertOk();
    $reserva = Reserva::where('email_cliente', 'transferencia-auditoria@demo.local')->firstOrFail();
    expect((float) $reserva->total_pagado)->toBe(0.0)->and($reserva->estado)->toBe(EstadoReserva::PENDIENTE);
});

test('FI-14 el detalle impreso debe mostrar el IVA y total de la linea almacenada', function () {
    $f = escenarioIntegridadFiscal();
    $venta = emitirIntegridadFiscal($f);
    $factura = $venta->facturas->firstOrFail();
    $factura->load('detalles', 'serie', 'autorizacionDgi', 'moneda', 'venta', 'cuenta', 'emisor');
    $html = view('reports.facturacion.factura-fiscal', [
        'factura' => $factura, 'datosHotel' => HotelInfo::getBaseData(),
        'codigoReporte' => 'HTB-FAC-001', 'nombreReporte' => 'Factura DEMO', 'simboloMoneda' => 'C$',
        'totalEnLetras' => 'SOLO DEMOSTRACIÓN', 'datosReceptor' => ['razon_social' => 'Cliente DEMO'],
        'barcodeBase64' => '', 'fechaEmision' => now()->format('d/m/Y H:i'), 'pageMarginTop' => 8, 'pageMarginRight' => 8, 'pageMarginBottom' => 10, 'pageMarginLeft' => 8,
    ])->render();
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $total = $xpath->query('//tr[td/strong[contains(., "auditor")]]/td[last()]');
    expect($total->length)->toBe(1)->and(trim($total->item(0)->textContent))->toContain(number_format((float) $factura->detalles->firstOrFail()->total_linea, 2));
});
