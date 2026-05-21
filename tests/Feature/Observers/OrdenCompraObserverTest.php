<?php

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Compras\Solicitud;
use App\Models\User;
use Database\Seeders\TasaCambioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Seed currencies to avoid foreign key constraints (moneda_id = 2 default)
    $seeder = new TasaCambioSeeder;
    $seeder->run();
});

it('al crear la orden de compra actualiza el flujo de cotizaciones correctamente', function () {
    $colaborador = Colaborador::factory()->create();
    $departamento = Catalogo::factory()->create();

    $solicitud = Solicitud::create([
        'codigo' => 'SOL-OBS-TEST',
        'colaborador_id' => $colaborador->id,
        'departamento_solicitante_id' => $departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Motivo de prueba',
        'estado' => EstadoSolicitud::Aprobada,
    ]);

    $proveedor = Proveedor::factory()->create();

    // Cotización ganadora
    $cotizacionGanadora = Cotizacion::create([
        'solicitud_id' => $solicitud->id,
        'proveedor_id' => $proveedor->id,
        'fecha_cotizacion' => now(),
        'subtotal' => 100.0,
        'total' => 100.0,
        'estado' => EstadoCotizacion::Activa,
    ]);

    // Cotización perdedora
    $cotizacionPerdedora = Cotizacion::create([
        'solicitud_id' => $solicitud->id,
        'proveedor_id' => $proveedor->id,
        'fecha_cotizacion' => now(),
        'subtotal' => 100.0,
        'total' => 100.0,
        'estado' => EstadoCotizacion::Activa,
    ]);

    // Crear orden vinculada
    $orden = OrdenCompra::factory()->create([
        'solicitud_id' => $solicitud->id,
        'cotizacion_id' => $cotizacionGanadora->id,
    ]);

    expect($cotizacionGanadora->fresh()->estado)->toBe(EstadoCotizacion::Aceptada);
    expect($cotizacionPerdedora->fresh()->estado)->toBe(EstadoCotizacion::Rechazada);
});

it('recalcula subtotales e impuestos cuando la orden de compra se marca como Recibida', function () {
    $producto = Producto::factory()->create();

    $orden = OrdenCompra::factory()->create([
        'estado' => EstadoOrdenCompra::Emitida,
        'subtotal' => 100.0,
        'impuestos' => 16.0,
        'total' => 116.0,
    ]);

    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $orden->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 10.0,
        'subtotal' => 100.0,
    ]);

    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Completa,
    ]);

    // Recibió solo 5 unidades
    RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $producto->id,
        'cantidad_recibida' => 5,
        'cantidad_rechazada' => 0,
    ]);

    // Actualizar estado del PO a Recibida
    $orden->update(['estado' => EstadoOrdenCompra::Recibida]);

    $ordenItemRefrescado = $ordenItem->fresh();
    expect((float) $ordenItemRefrescado->cantidad)->toBe(5.0);
    expect((float) $ordenItemRefrescado->subtotal)->toBe(50.0);

    $ordenRefrescada = $orden->fresh();
    expect((float) $ordenRefrescada->subtotal)->toBe(50.0);
    expect((float) $ordenRefrescada->impuestos)->toBe(8.0);
    expect((float) $ordenRefrescada->total)->toBe(58.0);
});
