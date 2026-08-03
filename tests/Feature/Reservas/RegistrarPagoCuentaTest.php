<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Interactors\Cuentas\RegistrarPagoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;

test('registra un pago correctamente y actualiza el saldo de la cuenta', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-PAGO-01',
        'nombre_cliente' => 'Cliente Pagador',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'check_in_at' => now(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-2026-000777',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);

    $detalle = app(RegistrarDetalleCuenta::class);
    $detalle->ejecutar(
        cuenta: $cuenta,
        concepto: 'Hospedaje Noches',
        precioUnitario: 1000.00,
    );

    $interactor = app(RegistrarPagoCuenta::class);
    $pago = $interactor->ejecutar(
        cuenta: $cuenta,
        metodoPago: MetodoPago::TARJETA_CREDITO,
        monto: 600.00,
        referenciaTransaccion: 'TXN-998822',
        estado: EstadoPago::APLICADO,
    );

    expect((float) $pago->monto)->toBe(600.0)
        ->and((float) $cuenta->refresh()->total_pagado)->toBe(600.0)
        ->and((float) $cuenta->saldo)->toBe(550.0);
});

test('rechaza registrar pagos menores o iguales a cero', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-PAGO-02',
        'nombre_cliente' => 'Cliente Pagador 2',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'check_in_at' => now(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-2026-000778',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);

    $interactor = app(RegistrarPagoCuenta::class);
    $interactor->ejecutar(
        cuenta: $cuenta,
        metodoPago: MetodoPago::EFECTIVO,
        monto: 0.00,
    );
})->throws(DomainException::class, 'mayor a cero');
