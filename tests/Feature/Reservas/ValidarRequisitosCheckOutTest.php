<?php

declare(strict_types=1);

use App\BusinessLogic\CheckOut\ValidarRequisitosCheckOut;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;

test('rechaza el check-out si la estancia no esta activa ni extendida', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-OUT-01',
        'nombre_cliente' => 'Cliente Out 1',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::FINALIZADA,
        'check_in_at' => now(),
    ]);

    $validator = new ValidarRequisitosCheckOut;
    $validator->validar($estancia);
})->throws(DomainException::class, 'estancias activas o extendidas');

test('permite el check-out con saldo cero en la cuenta de estancia', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-OUT-02',
        'nombre_cliente' => 'Cliente Out 2',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'cantidad_llaves' => 2,
        'check_in_at' => now(),
    ]);

    CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-2026-000002',
        'abierta_at' => now(),
        'saldo' => 0,
    ]);

    $validator = new ValidarRequisitosCheckOut;
    expect(fn () => $validator->validar($estancia, ['llaves_devueltas' => 2]))->not->toThrow(Exception::class);
});

test('permite el check-out con saldo pendiente si cuenta con credito corporativo autorizado', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-OUT-03',
        'nombre_cliente' => 'Corporativo ACME',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-02',
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'estado' => EstadoEstancia::ACTIVA,
        'cantidad_llaves' => 1,
        'check_in_at' => now(),
    ]);

    CuentaEstancia::query()->create([
        'estancia_id' => $estancia->id,
        'numero_folio' => 'CTA-2026-000003',
        'abierta_at' => now(),
        'saldo' => 1500,
    ]);

    $validator = new ValidarRequisitosCheckOut;
    expect(fn () => $validator->validar($estancia, [
        'llaves_devueltas' => 1,
        'credito_autorizado' => true,
    ]))->not->toThrow(Exception::class);
});
