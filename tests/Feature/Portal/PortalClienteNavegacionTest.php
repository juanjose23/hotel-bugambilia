<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;

test('la ruta de listado de reservas en el portal responde correctamente', function (): void {
    $response = $this->get('/portal/reservas');

    $response->assertStatus(200);
});

test('la ruta de detalle de reserva en el portal responde correctamente con codigo o para usuario autenticado', function (): void {
    $habitacion = Habitacion::query()->first() ?? Habitacion::factory()->create();
    $moneda = Moneda::query()->first() ?? Moneda::create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'tasa_cambio' => 1.0,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-NAV-TEST-1',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CONFIRMADA,
        'nombre_cliente' => 'Nav Test',
        'email_cliente' => 'nav.test@example.com',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'fecha_check_in' => now()->addDays(1),
        'fecha_check_out' => now()->addDays(3),
        'subtotal' => 100.0,
        'total' => 100.0,
        'total_pagado' => 100.0,
        'saldo' => 0.0,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
        'adultos' => 1,
        'ninos' => 0,
    ]);

    $response = $this->get("/portal/reservas/{$reserva->id}?codigo=RES-NAV-TEST-1");

    $response->assertStatus(200);
});
