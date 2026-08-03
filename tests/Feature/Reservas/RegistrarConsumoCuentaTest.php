<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;

test('registra un consumo correctamente en una cuenta abierta', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONSUMO-01',
        'nombre_cliente' => 'Cliente Consumo',
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
        'numero_cuenta' => 'CTA-2026-000088',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'estado' => EstadoCuenta::ABIERTA,
        'limite_autorizado' => 1000,
        'abierta_at' => now(),
    ]);

    $interactor = app(RegistrarDetalleCuenta::class);
    $detalle = $interactor->ejecutar(
        cuenta: $cuenta,
        concepto: 'Cena Buffet Internacional',
        precioUnitario: 250.00,
        cantidad: 2,
    );

    expect((float) $detalle->total)->toBe(500.0)
        ->and((float) $cuenta->refresh()->subtotal)->toBe(500.0)
        ->and((float) $cuenta->total)->toBe(575.0)
        ->and((float) $cuenta->saldo)->toBe(575.0);
});

test('rechaza registrar un consumo si la cuenta esta en estado solicitada', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONSUMO-02',
        'nombre_cliente' => 'Cliente Solicitado',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::PROGRAMADA,
        'check_in_at' => now(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-2026-000089',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'estado' => EstadoCuenta::SOLICITADA,
        'abierta_at' => now(),
    ]);

    $interactor = app(RegistrarDetalleCuenta::class);
    $interactor->ejecutar(
        cuenta: $cuenta,
        concepto: 'Bebidas Minibar',
        precioUnitario: 150.00,
    );
})->throws(DomainException::class);
