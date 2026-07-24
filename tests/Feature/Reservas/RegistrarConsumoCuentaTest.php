<?php

declare(strict_types=1);

use App\Enums\Estancias\CategoriaConsumo;
use App\Enums\Estancias\EstadoCuentaEstancia;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\CuentasEstancia\RegistrarConsumoCuenta;
use App\Repository\Models\Estancias\CuentaEstancia;
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

    $cuenta = CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-2026-000088',
        'estado' => EstadoCuentaEstancia::ABIERTA,
        'limite_autorizado' => 1000,
        'abierta_at' => now(),
    ]);

    $interactor = new RegistrarConsumoCuenta;
    $movimiento = $interactor->ejecutar(
        cuenta: $cuenta,
        categoria: CategoriaConsumo::RESTAURANTE,
        concepto: 'Cena Buffet Internacional',
        precioUnitario: 250.00,
        cantidad: 2,
        moduloOrigen: 'restaurante'
    );

    expect((float) $movimiento->monto)->toBe(500.0)
        ->and((float) $cuenta->refresh()->total_cargos)->toBe(500.0)
        ->and((float) $cuenta->saldo)->toBe(500.0);
});

test('rechaza registrar un consumo si la cuenta esta en estado solicitada o no abierta', function (): void {
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

    $cuenta = CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-2026-000089',
        'estado' => EstadoCuentaEstancia::SOLICITADA,
        'abierta_at' => now(),
    ]);

    $interactor = new RegistrarConsumoCuenta;
    $interactor->ejecutar(
        cuenta: $cuenta,
        categoria: CategoriaConsumo::MINIBAR,
        concepto: 'Bebidas Minibar',
        precioUnitario: 150.00
    );
})->throws(DomainException::class, 'cuentas abiertas');
