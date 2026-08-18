<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Operaciones\SincronizarCuentaReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;

test('sincroniza totales de cuenta desde reserva', function (): void {
    $moneda = Moneda::query()->create([
        'codigo' => 'USD-SYNC',
        'nombre' => 'Dolar Sync',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-SYNC-'.Str::random(5),
        'nombre_cliente' => 'Cliente Sync',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::CONFIRMADA,
        'moneda_id' => $moneda->id,
        'total' => 300.00,
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-SYNC-'.Str::random(5),
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 0,
        'total' => 0,
        'total_pagado' => 0,
        'saldo' => 0,
        'abierta_at' => now(),
    ]);

    $resultado = app(SincronizarCuentaReserva::class)->ejecutar($reserva, $cuenta);

    expect($resultado)->toBeInstanceOf(Reserva::class);
    expect($cuenta->fresh()->reserva_id)->toBe($reserva->id);
});
