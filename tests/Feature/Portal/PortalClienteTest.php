<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\User;

test('un huesped autenticado puede ver el dashboard del portal', function (): void {
    $user = User::factory()->create([
        'name' => 'Juan Huésped',
        'email' => 'juan.huesped@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('portal/Dashboard')
        ->has('cliente')
        ->has('estadisticas')
        ->has('reservas_activas')
        ->has('historial_reservas')
    );
});

test('un huesped puede ver el listado de sus reservas', function (): void {
    $user = User::factory()->create([
        'email' => 'maria.huesped@example.com',
    ]);

    $habitacion = Habitacion::query()->first() ?? Habitacion::factory()->create();
    $moneda = Moneda::query()->first() ?? Moneda::create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'tasa_cambio' => 1.0,
    ]);

    Reserva::create([
        'codigo_reserva' => 'RES-TEST-PORTAL-1',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CONFIRMADA,
        'nombre_cliente' => 'Maria Huésped',
        'email_cliente' => 'maria.huesped@example.com',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDays(2),
        'fecha_check_out' => now()->addDays(5),
        'subtotal' => 300.0,
        'total' => 300.0,
        'total_pagado' => 150.0,
        'saldo' => 150.0,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
        'adultos' => 2,
        'ninos' => 0,
    ]);

    $response = $this->actingAs($user)->get(route('portal.reservas.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('portal/MisReservas')
        ->has('reservas_activas', 1)
    );
});

test('un huesped puede consultar el detalle de su reserva', function (): void {
    $user = User::factory()->create([
        'email' => 'carlos.huesped@example.com',
    ]);

    $habitacion = Habitacion::query()->first() ?? Habitacion::factory()->create();
    $moneda = Moneda::query()->first() ?? Moneda::create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'tasa_cambio' => 1.0,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-TEST-PORTAL-2',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CONFIRMADA,
        'nombre_cliente' => 'Carlos Huésped',
        'email_cliente' => 'carlos.huesped@example.com',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDays(1),
        'fecha_check_out' => now()->addDays(3),
        'subtotal' => 200.0,
        'total' => 200.0,
        'total_pagado' => 200.0,
        'saldo' => 0.0,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
        'adultos' => 1,
        'ninos' => 0,
    ]);

    $response = $this->actingAs($user)->get(route('portal.reservas.show', ['id' => $reserva->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('portal/ReservaDetalle')
        ->where('reserva.codigo_reserva', 'RES-TEST-PORTAL-2')
    );
});

test('un huesped puede registrar acompanantes para su estancia', function (): void {
    $user = User::factory()->create([
        'email' => 'ana.huesped@example.com',
    ]);

    $habitacion = Habitacion::query()->first() ?? Habitacion::factory()->create();
    $moneda = Moneda::query()->first() ?? Moneda::create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'tasa_cambio' => 1.0,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-TEST-PORTAL-3',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CONFIRMADA,
        'nombre_cliente' => 'Ana Huésped',
        'email_cliente' => 'ana.huesped@example.com',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDays(1),
        'fecha_check_out' => now()->addDays(4),
        'subtotal' => 250.0,
        'total' => 250.0,
        'total_pagado' => 250.0,
        'saldo' => 0.0,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
        'adultos' => 2,
        'ninos' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('portal.reservas.acompanantes.store', ['id' => $reserva->id]), [
        'acompanantes' => [
            ['nombre' => 'Pedro Acompañante', 'identificacion' => '123-456', 'tipo' => 'adulto'],
            ['nombre' => 'Lucía Niña', 'identificacion' => '', 'tipo' => 'nino'],
        ],
    ]);

    $response->assertRedirect(route('portal.reservas.show', ['id' => $reserva->id]));

    $reserva->refresh();
    expect($reserva->acompanantes)->toHaveCount(2)
        ->and($reserva->acompanantes[0]['nombre'])->toBe('Pedro Acompañante');
});

test('un huesped puede solicitar un servicio adicional a su estancia', function (): void {
    $user = User::factory()->create([
        'email' => 'luis.huesped@example.com',
    ]);

    $habitacion = Habitacion::query()->first() ?? Habitacion::factory()->create();
    $moneda = Moneda::query()->first() ?? Moneda::create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'tasa_cambio' => 1.0,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-TEST-PORTAL-4',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CONFIRMADA,
        'nombre_cliente' => 'Luis Huésped',
        'email_cliente' => 'luis.huesped@example.com',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDays(1),
        'fecha_check_out' => now()->addDays(4),
        'subtotal' => 250.0,
        'total' => 250.0,
        'total_pagado' => 250.0,
        'saldo' => 0.0,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
        'adultos' => 1,
        'ninos' => 0,
    ]);

    $servicio = Servicio::create([
        'codigo' => 'SRV-ROOM-1',
        'nombre' => 'Desayuno Colonial a la Habitación',
        'descripcion' => 'Desayuno típico servido en la suite',
        'estado' => 1,
    ]);

    Precio::create([
        'priceable_type' => Servicio::class,
        'priceable_id' => $servicio->id,
        'moneda_id' => $moneda->id,
        'precio' => 15.00,
        'fecha_inicio' => now()->startOfDay(),
    ]);

    $response = $this->actingAs($user)->post(route('portal.reservas.servicios.store', ['id' => $reserva->id]), [
        'servicio_id' => $servicio->id,
        'cantidad' => 2,
        'notas' => 'Por favor servir a las 8:00 AM',
    ]);

    $response->assertRedirect(route('portal.reservas.show', ['id' => $reserva->id]));

    $reserva->refresh();
    $cuenta = $reserva->cuentas()->where('estado', EstadoCuenta::ABIERTA)->first();
    expect($cuenta)->not->toBeNull()
        ->and((float) $cuenta->saldo)->toBe(30.00)
        ->and($cuenta->detalles)->toHaveCount(1)
        ->and($cuenta->detalles[0]->concepto)->toContain('Desayuno Colonial');
});

test('un huesped puede actualizar sus datos de perfil desde el portal', function (): void {
    $user = User::factory()->create([
        'name' => 'Roberto Original',
        'email' => 'roberto@example.com',
    ]);

    $response = $this->actingAs($user)->post(route('portal.perfil.update'), [
        'nombre' => 'Roberto Actualizado',
        'email' => 'roberto.nuevo@example.com',
        'telefono' => '+505 8899 0011',
        'identificacion' => '001-123456-0001A',
    ]);

    $response->assertRedirect(route('portal.perfil'));

    $user->refresh();
    expect($user->name)->toBe('Roberto Actualizado')
        ->and($user->email)->toBe('roberto.nuevo@example.com')
        ->and($user->persona?->telefono)->toBe('+505 8899 0011');
});
