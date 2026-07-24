<?php

declare(strict_types=1);

use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Estancias\EstadoPago;
use App\Enums\Estancias\MetodoPago;
use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\CuentasEstancia\RegistrarPagoCuenta;
use App\Repository\Models\Estancias\CuentaEstancia;
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

    $cuenta = CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-2026-000777',
        'estado' => EstadoCuentaEstancia::ABIERTA,
        'abierta_at' => now(),
    ]);

    $cuenta->movimientos()->create([
        'tipo' => TipoMovimientoCuenta::CARGO,
        'concepto' => 'Hospedaje Noches',
        'monto' => 1000.00,
    ]);

    $interactor = new RegistrarPagoCuenta;
    $movimiento = $interactor->ejecutar(
        cuenta: $cuenta,
        metodoPago: MetodoPago::TARJETA_CREDITO,
        monto: 600.00,
        concepto: 'Abono con Tarjeta Visa',
        referencia: 'TXN-998822',
        estado: EstadoPago::APLICADO
    );

    expect((float) $movimiento->monto)->toBe(600.0)
        ->and((float) $cuenta->refresh()->total_pagos)->toBe(600.0)
        ->and((float) $cuenta->saldo)->toBe(400.0);
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

    $cuenta = CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-2026-000778',
        'estado' => EstadoCuentaEstancia::ABIERTA,
        'abierta_at' => now(),
    ]);

    $interactor = new RegistrarPagoCuenta;
    $interactor->ejecutar(
        cuenta: $cuenta,
        metodoPago: MetodoPago::EFECTIVO,
        monto: 0.00
    );
})->throws(DomainException::class, 'mayor a cero');
