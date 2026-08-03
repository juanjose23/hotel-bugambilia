<?php

declare(strict_types=1);

use App\BusinessLogic\Cuentas\ValidarCreditoHabitacion;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;

test('valida correctamente el limite de credito autorizado de una habitacion', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CREDITO-01',
        'nombre_cliente' => 'Huésped Crédito',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
        'solicita_cuenta' => true,
        'limite_cuenta_solicitado' => 500.0,
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'check_in_at' => now(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-HAB-000100',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'estado' => EstadoCuenta::ABIERTA,
        'limite_autorizado' => 500.0,
        'saldo' => 450.0,
        'abierta_at' => now(),
    ]);

    $validador = new ValidarCreditoHabitacion;

    // 1. Consumo dentro del límite (450 + 40 = 490 <= 500) pasa sin lanzar excepción
    expect(fn () => $validador->validar($estancia, $cuenta, 40.0))->not->toThrow(DomainException::class);

    // 2. Consumo sobre el límite (450 + 100 = 550 > 500) lanza DomainException
    expect(fn () => $validador->validar($estancia, $cuenta, 100.0))->toThrow(DomainException::class, 'excede el límite de crédito');
});

test('rechaza transferir pedido a habitacion sin solicita_cuenta autorizada', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CREDITO-02',
        'nombre_cliente' => 'Huésped Sin Cuenta',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
        'solicita_cuenta' => false,
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'check_in_at' => now(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-HAB-000101',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);

    $validador = new ValidarCreditoHabitacion;

    expect(fn () => $validador->validar($estancia, $cuenta, 50.0))->toThrow(DomainException::class, 'no tiene autorizada la apertura de cuenta');
});
