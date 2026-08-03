<?php

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoSolicitud;
use App\Notifications\Compras\NotificadorCompras;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Database\Factories\CatalogoFactory;
use Database\Seeders\MonedaSeeder;
use Database\Seeders\TasaCambioSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $persona = Persona::factory()->create();

    $this->user = User::factory()->create([
        'persona_id' => $persona->id,
    ]);
    $this->actingAs($this->user);

    // Seed currencies to avoid foreign key constraints (moneda_id = 2 default)
    $this->seed([
        MonedaSeeder::class,
        TasaCambioSeeder::class,
    ]);

    // Assign a compras role so the user is found by DestinatariosCompra
    Role::firstOrCreate(['name' => 'compras_encargado', 'guard_name' => 'web']);
    $this->user->assignRole('compras_encargado');

    $this->notificador = app(NotificadorCompras::class);
    $this->proveedor = Proveedor::factory()->create();

    // Link colaborador to the shared persona so notifications are sent to the logged-in user!
    $this->colaborador = Colaborador::factory()->create([
        'persona_id' => $persona->id,
    ]);
    $this->departamento = CatalogoFactory::new()->create();
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
    expect($notification->data['body'])->toBe('Se ha registrado la solicitud SOL-TEST-001 por '.$this->colaborador->persona->nombre_completo.'.');
    expect($notification->data['icon'])->toBe('heroicon-s-information-circle');
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
    expect($notification->data['body'])->toBe('La solicitud SOL-TEST-002 ha sido aprobada.');
    expect($notification->data['icon'])->toBe('heroicon-s-check-circle');
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
        'moneda_id' => Moneda::where('codigo', 'NIO')->value('id'),
    ]);

    $this->notificador->cotizacionCreada($cotizacion);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Nueva Cotización');
    $nombreProveedor = $this->proveedor->persona->nombre_completo;
    expect($notification->data['body'])->toBe("Se ha registrado una cotización de {$nombreProveedor} para la solicitud SOL-TEST-003.");
    expect($notification->data['icon'])->toBe('heroicon-s-information-circle');
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
        'moneda_id' => Moneda::where('codigo', 'NIO')->value('id'),
    ]);

    $this->notificador->ganadorSeleccionado($cotizacion);

    $notification = $this->user->notifications()->first();
    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Ganador Seleccionado');
    $nombreProveedor = $this->proveedor->persona->nombre_completo;
    expect($notification->data['body'])->toBe("Se ha elegido la cotización de {$nombreProveedor} como ganadora para la solicitud SOL-TEST-004.");
    expect($notification->data['icon'])->toBe('heroicon-s-check-circle');
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
    $nombreProveedor = $orden->proveedor->persona->nombre_completo;
    expect($notification->data['title'])->toBe('Orden de Compra Creada');
    expect($notification->data['body'])->toBe("Se ha creado la Orden de Compra OC-TEST-001 en borrador para el proveedor {$nombreProveedor}.");
    expect($notification->data['icon'])->toBe('heroicon-s-shopping-cart');
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
    expect($notification->data['body'])->toBe('La recepción REC-TEST-001 ha sido completada para la OC OC-TEST-002.');
    expect($notification->data['icon'])->toBe('heroicon-s-truck');
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
    $nombreProveedor = $devolucion->ordenCompra->proveedor->persona->nombre_completo;
    expect($notification->data['body'])->toBe("La devolución DEV-TEST-001 ha sido confirmada para el proveedor {$nombreProveedor}.");
    expect($notification->data['icon'])->toBe('heroicon-s-check-circle');
    expect($notification->data['actions'][0]['url'])->toBe(url("/admin/compras/devoluciones/{$devolucion->id}"));
});
