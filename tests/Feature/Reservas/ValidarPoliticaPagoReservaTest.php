<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\ValidarPoliticaPagoReserva;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;

test('validar politica pago exige 50 por ciento si tipo pago es ABONO_50', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-POL-01',
        'nombre_cliente' => 'Cliente Politica',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now(),
        'fecha_check_out' => now()->addDays(2),
        'estado' => EstadoReserva::PENDIENTE,
        'subtotal' => 200.00,
        'total' => 200.00,
        'tipo_pago' => TipoPagoReserva::ABONO_50,
        'total_pagado' => 40.00, // Menos del 50% (100.00)
    ]);

    $validator = new ValidarPoliticaPagoReserva;

    expect(fn () => $validator->validarMontoParaConfirmacion($reserva))
        ->toThrow(DomainException::class);
});

test('validar politica pago aprueba confirmacion cuando el abono cumple o supera el 50 por ciento', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-POL-02',
        'nombre_cliente' => 'Cliente Politica 2',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now(),
        'fecha_check_out' => now()->addDays(2),
        'estado' => EstadoReserva::PENDIENTE,
        'subtotal' => 200.00,
        'total' => 200.00,
        'tipo_pago' => TipoPagoReserva::ABONO_50,
        'total_pagado' => 100.00, // Exactamente 50%
    ]);

    $validator = new ValidarPoliticaPagoReserva;
    $validator->validarMontoParaConfirmacion($reserva);

    expect(true)->toBeTrue();
});
