<?php

declare(strict_types=1);

use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Operaciones\RegistrarCobroInicialReserva;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;

test('registra cobro inicial sin pago dejando reserva pendiente', function (): void {
    $moneda = Moneda::query()->create([
        'codigo' => 'USD-COBRO1',
        'nombre' => 'Dolar Cobro 1',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-COBRO-01',
        'nombre_cliente' => 'Cliente Cobro 1',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDays(2)->toDateString(),
        'fecha_check_out' => now()->addDays(4)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
        'subtotal' => 200.00,
        'total' => 200.00,
    ]);

    $resultado = app(RegistrarCobroInicialReserva::class)->ejecutar(
        reserva: $reserva,
        tipoPago: TipoPagoReserva::SIN_PAGO,
        monedaId: $moneda->id,
        metodoPago: null,
        referencia: null,
        usuarioId: null,
    );

    expect($resultado->estado)->toBe(EstadoReserva::PENDIENTE);
    expect((float) $resultado->total_pagado)->toBe(0.00);
});

test('registra cobro inicial pago completo confirmando reserva', function (): void {
    $moneda = Moneda::query()->create([
        'codigo' => 'USD-COBRO2',
        'nombre' => 'Dolar Cobro 2',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-COBRO-02',
        'nombre_cliente' => 'Cliente Cobro 2',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDays(2)->toDateString(),
        'fecha_check_out' => now()->addDays(4)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
        'subtotal' => 150.00,
        'total' => 150.00,
    ]);

    $resultado = app(RegistrarCobroInicialReserva::class)->ejecutar(
        reserva: $reserva,
        tipoPago: TipoPagoReserva::PAGO_COMPLETO,
        monedaId: $moneda->id,
        metodoPago: MetodoPago::EFECTIVO,
        referencia: 'REF-CASH-123',
        usuarioId: null,
        montoSolicitado: null,
    );

    expect($resultado->estado)->toBe(EstadoReserva::CONFIRMADA);
    expect((float) $resultado->total_pagado)->toBeGreaterThan(0.00);
});
