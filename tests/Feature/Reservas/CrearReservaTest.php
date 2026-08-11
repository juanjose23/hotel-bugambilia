<?php

declare(strict_types=1);

use App\Enums\Cuentas\MetodoPago;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\CheckIn\RegistrarCheckIn;
use App\Interactors\CheckOut\RegistrarCheckOut;
use App\Interactors\Reservas\Gestion\ConfirmarReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Pais;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Database\Factories\Habitaciones\HabitacionFactory;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\EspacioSeeder;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\MonedaSeeder;
use Database\Seeders\PaisSeeder;
use Database\Seeders\ServicioSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;

beforeEach(function (): void {
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        HabitacionSeeder::class,
        ServicioSeeder::class,
        EspacioSeeder::class,
    ]);
});

test('agrega servicios y espacios adicionales como detalles hijos', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $servicio = Servicio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();
    $espacio = Espacio::query()
        ->whereHas('precios', fn ($query) => $query->where('tipo_precio', 'base'))
        ->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva con adicionales',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-11-10',
        'fecha_check_out' => '2026-11-11',
        'adultos' => 2,
    ], [
        ['servicio_id' => $servicio->id, 'cantidad' => 2],
    ], [
        ['espacio_id' => $espacio->id, 'cantidad' => 1],
    ]);

    $detalles = $reserva->detalles()->orderBy('id')->get();
    $principal = $detalles->firstOrFail();

    expect($detalles)->toHaveCount(3)
        ->and($detalles->skip(1)->pluck('parent_id')->unique()->all())->toBe([$principal->id])
        ->and((float) $reserva->subtotal)->toBe((float) $detalles->sum('subtotal'))
        ->and((float) $reserva->total)->toBeGreaterThanOrEqual((float) $reserva->subtotal);
});

test('agrega mesas sugeridas al crear reserva de restaurante con capacidad insuficiente', function (): void {
    $ambiente = Espacio::query()->create([
        'codigo' => 'AMB-AUTO-01',
        'nombre' => 'Ambiente Auto',
        'tipo' => TipoEspacio::AMBIENTE,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => false,
        'capacidad_personas' => 0,
    ]);

    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-AUTO-01',
        'nombre' => 'Mesa Auto 01',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 2,
    ]);
    $mesaSugerida = Espacio::query()->create([
        'codigo' => 'MESA-AUTO-02',
        'nombre' => 'Mesa Auto 02',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);
    $mesaDisponible = Espacio::query()->create([
        'codigo' => 'MESA-AUTO-03',
        'nombre' => 'Mesa Auto 03',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);

    $recursoOcupado = app(ReservaRepositorioInterface::class)->resolverRecurso(TipoReserva::RESTAURANTE, (int) $mesaSugerida->id);
    $reservaExistente = Reserva::query()->create([
        'codigo_reserva' => 'RES-MESA-OCUPADA',
        'nombre_cliente' => 'Reserva ya tomada',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'espacio_id' => $mesaSugerida->id,
        'fecha_check_in' => '2026-11-10',
        'hora_reserva' => '13:00',
        'adultos' => 4,
        'estado' => EstadoReserva::CONFIRMADA,
        'total' => 100,
    ]);
    $reservaExistente->detalles()->create([
        'reservable_id' => $recursoOcupado->id,
        'estado' => EstadoReservaDetalle::CONFIRMADO,
        'fecha_inicio' => '2026-11-10 13:00:00',
        'fecha_fin' => '2026-11-10 15:00:00',
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
    ]);

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva restaurante grupo',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-10',
        'hora_reserva' => '13:00',
        'duracion_horas' => 2,
        'adultos' => 6,
    ]);

    $idsMesasDetalle = $reserva->detalles()
        ->with('reservable.espacio')
        ->get()
        ->map(fn ($detalle): ?int => $detalle->reservable?->espacio?->id)
        ->filter()
        ->values()
        ->all();

    expect($idsMesasDetalle)->toContain((int) $mesaPrincipal->id)
        ->and($idsMesasDetalle)->not->toContain((int) $mesaSugerida->id)
        ->and($idsMesasDetalle)->toContain((int) $mesaDisponible->id)
        ->and($reserva->detalles()->whereNotNull('parent_id')->count())->toBe(1)
        ->and($mesaSugerida->refresh()->estado)->toBe(EstadoEspacio::Disponible)
        ->and($mesaDisponible->refresh()->reservable_id)->not->toBeNull();
});

test('respeta la mesa adicional elegida por el usuario aunque exista otra sugerencia', function (): void {
    $ambiente = Espacio::query()->create([
        'codigo' => 'AMB-MANUAL-01',
        'nombre' => 'Ambiente Manual',
        'tipo' => TipoEspacio::AMBIENTE,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => false,
        'capacidad_personas' => 0,
    ]);

    $mesaPrincipal = Espacio::query()->create([
        'codigo' => 'MESA-MANUAL-01',
        'nombre' => 'Mesa Manual 01',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 2,
    ]);
    $mesaSugerible = Espacio::query()->create([
        'codigo' => 'MESA-MANUAL-02',
        'nombre' => 'Mesa Manual 02',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);
    $mesaElegida = Espacio::query()->create([
        'codigo' => 'MESA-MANUAL-03',
        'nombre' => 'Mesa Manual 03',
        'tipo' => TipoEspacio::MESA,
        'padre_id' => $ambiente->id,
        'estado' => EstadoEspacio::Disponible,
        'reservable' => true,
        'capacidad_personas' => 4,
    ]);

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Reserva restaurante manual',
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'espacio_id' => $mesaPrincipal->id,
        'fecha_check_in' => '2026-11-12',
        'hora_reserva' => '14:00',
        'duracion_horas' => 2,
        'adultos' => 6,
    ], [], [
        ['espacio_id' => $mesaElegida->id, 'cantidad' => 1],
    ]);

    $idsMesasDetalle = $reserva->detalles()
        ->with('reservable.espacio')
        ->get()
        ->map(fn ($detalle): ?int => $detalle->reservable?->espacio?->id)
        ->filter()
        ->values()
        ->all();

    expect($idsMesasDetalle)->toContain((int) $mesaPrincipal->id)
        ->and($idsMesasDetalle)->toContain((int) $mesaElegida->id)
        ->and($idsMesasDetalle)->not->toContain((int) $mesaSugerible->id)
        ->and($mesaElegida->refresh()->reservable_id)->not->toBeNull();
});

test('calcula el total de la habitación con la tarifa del servidor', function (): void {
    $habitacion = Habitacion::query()->with('precios.moneda')->firstOrFail();
    $tarifaBase = (float) $habitacion->precios
        ->first(fn ($precio): bool => $precio->moneda->es_predeterminada)
        ?->precio;

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Juan José',
        'telefono_cliente' => '+505 8888 8888',
        'email_cliente' => 'juan@ejemplo.com',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => now()->addDays(5)->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(7)->format('Y-m-d'),
        'adultos' => 2,
        'ninos' => 0,
        'huespedes' => [
            ['nombre' => 'Ana José', 'identificacion' => 'ID-100', 'tipo' => 'adulto'],
        ],
        'total' => 0.01,
    ]);

    expect($reserva)->toBeInstanceOf(Reserva::class)
        ->and($reserva->estado)->toBe(EstadoReserva::PENDIENTE)
        ->and((float) $reserva->subtotal)->toBe(round($tarifaBase * 2, 2))
        ->and((float) $reserva->total)->toBe(round($tarifaBase * 2 * 1.15, 2))
        ->and($reserva->codigo_reserva)->toStartWith('HTB-2026-');

    $detalle = $reserva->detalles()->with('huespedes')->firstOrFail();
    expect($detalle->reservable_id)->not->toBeNull()
        ->and($detalle->huespedes)->toHaveCount(1)
        ->and($detalle->huespedes->first()?->nombre)->toBe('Ana José')
        ->and($reserva->historialEstados()->count())->toBe(1);

    $this->assertModelExists($reserva);
});

test('ignora el total manipulado enviado al endpoint público', function (): void {
    $habitacion = Habitacion::query()->with('precios.moneda')->firstOrFail();
    $tarifaBase = (float) $habitacion->precios
        ->first(fn ($precio): bool => $precio->moneda->es_predeterminada)
        ?->precio;

    $response = $this->post(route('reservas.crear'), [
        'nombre_cliente' => 'María López',
        'telefono_cliente' => '+505 7777 7777',
        'email_cliente' => 'maria@ejemplo.com',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-09-10',
        'fecha_check_out' => '2026-09-12',
        'adultos' => 2,
        'total' => 0.01,
    ]);

    $response->assertRedirect();

    $reserva = Reserva::query()->where('email_cliente', 'maria@ejemplo.com')->firstOrFail();
    expect((float) $reserva->subtotal)->toBe(round($tarifaBase * 2, 2))
        ->and((float) $reserva->total)->toBe(round($tarifaBase * 2 * 1.15, 2));
});

test('registra un abono exacto del cincuenta por ciento con cargos de facturación', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();
    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Cliente con abono',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2026-12-01',
        'fecha_check_out' => '2026-12-03',
        'adultos' => 2,
        'tipo_pago_reserva' => TipoPagoReserva::ABONO_50->value,
        'metodo_pago_reserva' => MetodoPago::EFECTIVO->value,
        'moneda_id' => $moneda->id,
    ]);

    $cuenta = $reserva->cuentas()->with(['pagos', 'cargos'])->firstOrFail();

    expect($reserva->tipo_pago)->toBe(TipoPagoReserva::ABONO_50)
        ->and($reserva->estado)->toBe(EstadoReserva::CONFIRMADA)
        ->and($cuenta->cargos)->not->toBeEmpty()
        ->and((float) $cuenta->total_pagado)->toBe(round((float) $cuenta->total * 0.50, 2))
        ->and((float) $cuenta->saldo)->toBe(round((float) $cuenta->total - (float) $cuenta->total_pagado, 2))
        ->and($cuenta->pagos)->toHaveCount(1);
});

test('el pago completo liquida la reserva y no se registra como abono', function (): void {
    $habitacion = Habitacion::query()->firstOrFail();
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();

    $reserva = app(CrearReserva::class)->ejecutar([
        'nombre_cliente' => 'Cliente pago completo',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => '2027-01-10',
        'fecha_check_out' => '2027-01-11',
        'adultos' => 1,
        'tipo_pago_reserva' => TipoPagoReserva::PAGO_COMPLETO->value,
        'metodo_pago_reserva' => MetodoPago::TARJETA_CREDITO->value,
        'moneda_id' => $moneda->id,
    ]);

    $cuenta = $reserva->cuentas()->with('pagos')->firstOrFail();

    expect($reserva->tipo_pago)->toBe(TipoPagoReserva::PAGO_COMPLETO)
        ->and((float) $cuenta->total_pagado)->toBe((float) $cuenta->total)
        ->and((float) $cuenta->saldo)->toBe(0.0)
        ->and($cuenta->pagos->first()?->observaciones)->toContain('Pago completo');
});

test('un visitante no puede cancelar una reserva', function (): void {
    $reserva = Reserva::query()->create(datosReserva());

    $this->post(route('reservas.cancelar', $reserva))
        ->assertRedirect(route('login'));

    expect($reserva->fresh()?->estado)->toBe(EstadoReserva::PENDIENTE);
});

test('otro cliente no puede cancelar una reserva ajena', function (): void {
    $pais = Pais::query()->firstOrFail();
    $catalogo = Catalogo::query()->firstOrFail();
    $persona = Persona::factory()->create(['pais_id' => $pais->id]);
    $propietario = User::factory()->create(['persona_id' => $persona->id]);
    $otroCliente = User::factory()->create();
    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'estado' => EstadoGeneral::Activo,
    ]);
    $reserva = Reserva::query()->create(datosReserva(['cliente_id' => $cliente->id]));

    $this->actingAs($otroCliente)
        ->post(route('reservas.cancelar', $reserva))
        ->assertForbidden();

    expect($reserva->fresh()?->estado)->toBe(EstadoReserva::PENDIENTE);
});

test('el propietario puede cancelar su reserva', function (): void {
    $pais = Pais::query()->firstOrFail();
    $catalogo = Catalogo::query()->firstOrFail();
    $persona = Persona::factory()->create(['pais_id' => $pais->id]);
    $propietario = User::factory()->create(['persona_id' => $persona->id]);
    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'estado' => EstadoGeneral::Activo,
    ]);
    $reserva = Reserva::query()->create(datosReserva(['cliente_id' => $cliente->id]));

    $this->actingAs($propietario)
        ->post(route('reservas.cancelar', $reserva))
        ->assertRedirect();

    expect($reserva->fresh()?->estado)->toBe(EstadoReserva::CANCELADA);
});

test('una reserva sigue la secuencia confirmada check in y check out', function (): void {
    $reserva = Reserva::query()->create(datosReserva());

    app(ConfirmarReserva::class)->ejecutar($reserva);
    expect($reserva->refresh()->estado)->toBe(EstadoReserva::CONFIRMADA);

    app(RegistrarCheckIn::class)->ejecutar($reserva);
    expect($reserva->refresh()->estado)->toBe(EstadoReserva::CHECKED_IN);

    app(RegistrarCheckOut::class)->ejecutar($reserva);
    expect($reserva->refresh()->estado)->toBe(EstadoReserva::CHECKED_OUT)
        ->and($reserva->historialEstados()->count())->toBe(3);
});

test('no permite hacer check in sin confirmar primero', function (): void {
    $reserva = Reserva::query()->create(datosReserva());

    app(RegistrarCheckIn::class)->ejecutar($reserva);
})->throws(DomainException::class);

test('abre la estancia y la cuenta solicitada durante el check in', function (): void {
    $reserva = Reserva::query()->create(datosReserva([
        'solicita_cuenta' => true,
        'limite_cuenta_solicitado' => 2500,
    ]));

    app(ConfirmarReserva::class)->ejecutar($reserva);
    $estancia = app(RegistrarCheckIn::class)->ejecutar($reserva, null, [
        'cantidad_llaves' => 2,
    ]);

    expect($estancia)->toBeInstanceOf(Estancia::class)
        ->and($estancia->cantidad_llaves)->toBe(2)
        ->and($estancia->cuenta)->not->toBeNull()
        ->and((float) $estancia->cuenta?->limite_autorizado)->toBe(2500.0)
        ->and($estancia->cuenta?->numero_cuenta)->toStartWith('CTA-');
});

test('impide el check out cuando la cuenta tiene saldo pendiente', function (): void {
    $reserva = Reserva::query()->create(datosReserva(['solicita_cuenta' => true]));

    app(ConfirmarReserva::class)->ejecutar($reserva);
    $estancia = app(RegistrarCheckIn::class)->ejecutar($reserva);
    $estancia->cuenta?->update(['total_cargos' => 500, 'saldo' => 500]);

    app(RegistrarCheckOut::class)->ejecutar($reserva);
})->throws(DomainException::class, 'saldo pendiente');

/**
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosReserva(array $cambios = []): array
{
    /** @var Habitacion $habitacion */
    $habitacion = Habitacion::query()->first() ?? HabitacionFactory::new()->create([
        'codigo' => 'HAB-TEST-1',
        'nombre' => 'Habitación Test 101',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $moneda = Moneda::query()->where('es_predeterminada', true)->first();

    return array_merge([
        'codigo_reserva' => 'RES-TEST-'.str()->random(10),
        'nombre_cliente' => 'Cliente de prueba',
        'habitacion_id' => $habitacion->id,
        'tipo_reserva' => TipoReserva::HABITACION,
        'moneda_id' => $moneda?->id,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'adultos' => 1,
        'ninos' => 0,
        'estado' => EstadoReserva::PENDIENTE,
        'total' => 100,
    ], $cambios);
}
