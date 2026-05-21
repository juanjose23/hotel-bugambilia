<?php

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Colaboradores\Colaborador;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\DevolucionCompra;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\Personas\Persona;
use App\Models\User;
use App\Services\Compras\NotificadorCompras;
use Database\Seeders\TasaCambioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $persona = Persona::factory()->create();

    $this->user = User::factory()->create([
        'persona_id' => $persona->id,
    ]);
    $this->actingAs($this->user);

    // Seed currencies to avoid foreign key constraints (moneda_id = 2 default)
    $seeder = new TasaCambioSeeder;
    $seeder->run();

    $this->notificador = app(NotificadorCompras::class);
    $this->proveedor = Proveedor::factory()->create();

    // Link colaborador to the shared persona so notifications are sent to the logged-in user!
    $this->colaborador = Colaborador::factory()->create([
        'persona_id' => $persona->id,
    ]);
    $this->departamento = Catalogo::factory()->create();
});

it('despacha notificacion para solicitudCreada', function () {
    $solicitud = Solicitud::create([
        'codigo' => 'SOL-TEST-001',
        'colaborador_id' => $this->colaborador->id,
        'departamento_solicitante_id' => $this->departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Motivo de prueba',
        'estado' => EstadoSolicitud::Aprobada,
    ]);

    $this->notificador->solicitudCreada($solicitud);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Solicitud Creada');
    expect($notification->data['body'])->toBe('La solicitud SOL-TEST-001 ha sido creada exitosamente.');
    expect($notification->data['icon'])->toBe('heroicon-o-document-text');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/solicitudes/{$solicitud->id}"));
});

it('despacha notificacion para solicitudAprobada', function () {
    $solicitud = Solicitud::create([
        'codigo' => 'SOL-TEST-002',
        'colaborador_id' => $this->colaborador->id,
        'departamento_solicitante_id' => $this->departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Motivo de prueba',
        'estado' => EstadoSolicitud::Aprobada,
    ]);

    $this->notificador->solicitudAprobada($solicitud);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Solicitud Aprobada');
    expect($notification->data['body'])->toBe('La solicitud SOL-TEST-002 ha sido aprobada y está lista para cotizar.');
    expect($notification->data['icon'])->toBe('heroicon-o-check-circle');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/solicitudes/{$solicitud->id}"));
});

it('despacha notificacion para cotizacionCreada', function () {
    $solicitud = Solicitud::create([
        'codigo' => 'SOL-TEST-003',
        'colaborador_id' => $this->colaborador->id,
        'departamento_solicitante_id' => $this->departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Motivo de prueba',
        'estado' => EstadoSolicitud::Aprobada,
    ]);

    $cotizacion = Cotizacion::create([
        'solicitud_id' => $solicitud->id,
        'proveedor_id' => $this->proveedor->id,
        'fecha_cotizacion' => now(),
        'subtotal' => 100.0,
        'total' => 100.0,
        'estado' => EstadoCotizacion::Activa,
    ]);

    $this->notificador->cotizacionCreada($cotizacion);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Nueva Cotización');
    expect($notification->data['body'])->toBe("Se ha registrado una cotización de {$this->proveedor->codigo} para la solicitud SOL-TEST-003.");
    expect($notification->data['icon'])->toBe('heroicon-o-document-currency-dollar');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/cotizaciones/{$cotizacion->id}"));
});

it('despacha notificacion para ganadorSeleccionado', function () {
    $solicitud = Solicitud::create([
        'codigo' => 'SOL-TEST-004',
        'colaborador_id' => $this->colaborador->id,
        'departamento_solicitante_id' => $this->departamento->id,
        'fecha_solicitud' => now(),
        'fecha_necesita' => now()->addDays(7),
        'motivo' => 'Motivo de prueba',
        'estado' => EstadoSolicitud::Aprobada,
    ]);

    $cotizacion = Cotizacion::create([
        'solicitud_id' => $solicitud->id,
        'proveedor_id' => $this->proveedor->id,
        'fecha_cotizacion' => now(),
        'subtotal' => 100.0,
        'total' => 100.0,
        'estado' => EstadoCotizacion::Activa,
    ]);

    $this->notificador->ganadorSeleccionado($cotizacion);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Ganador Seleccionado');
    expect($notification->data['body'])->toBe("El proveedor {$this->proveedor->codigo} ha sido seleccionado como ganador de la solicitud SOL-TEST-004.");
    expect($notification->data['icon'])->toBe('heroicon-o-trophy');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/cotizaciones/{$cotizacion->id}"));
});

it('despacha notificacion para ordenCreada', function () {
    $orden = OrdenCompra::factory()->create([
        'codigo' => 'OC-TEST-001',
        'total' => 1250.50,
    ]);

    $this->notificador->ordenCreada($orden);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Orden de Compra Creada');
    expect($notification->data['body'])->toBe('Se ha creado la orden OC-TEST-001 por un total de $1,250.50.');
    expect($notification->data['icon'])->toBe('heroicon-o-shopping-cart');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/ordenes-compra/{$orden->id}"));
});

it('despacha notificacion para recepcionCompletada', function () {
    $orden = OrdenCompra::factory()->create([
        'codigo' => 'OC-TEST-002',
    ]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'codigo' => 'REC-TEST-001',
    ]);

    $this->notificador->recepcionCompletada($recepcion);

    $notification = $this->user->notifications()->where('data->title', 'Recepción Completada')->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Recepción Completada');
    expect($notification->data['body'])->toBe('La recepción REC-TEST-001 ha sido completada exitosamente. La orden OC-TEST-002 ha sido marcada como Recibida.');
    expect($notification->data['icon'])->toBe('heroicon-o-check-badge');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/recepciones/{$recepcion->id}"));
});

it('despacha notificacion para devolucionConfirmada', function () {
    $orden = OrdenCompra::factory()->create([
        'codigo' => 'OC-TEST-003',
    ]);
    $devolucion = DevolucionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'codigo' => 'DEV-TEST-001',
    ]);

    $this->notificador->devolucionConfirmada($devolucion);

    $notification = $this->user->notifications()->where('data->title', 'Devolución Confirmada')->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Devolución Confirmada');
    expect($notification->data['body'])->toBe('La devolución DEV-TEST-001 vinculada a la orden OC-TEST-003 ha sido confirmada. Se ha retirado el stock físico y liberado el saldo del contrato.');
    expect($notification->data['icon'])->toBe('heroicon-o-check-circle');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/devoluciones/{$devolucion->id}"));
});
