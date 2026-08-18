<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Gestion\CancelarReservaPublica;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;

test('cancela reserva publica y retorna estado cancelado', function (): void {
    $moneda = Moneda::query()->create([
        'codigo' => 'USDC',
        'nombre' => 'Dolar Cancel',
        'simbolo' => '$',
        'es_predeterminada' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-PUB-CANCEL-'.Str::random(5),
        'nombre_cliente' => 'Cliente Pub Cancel',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
        'moneda_id' => $moneda->id,
        'total' => 0,
    ]);

    $data = new CancelarReservaHabitacionData(
        reservaId: $reserva->id,
        motivo: 'Cancelacion publica',
    );

    $resultado = app(CancelarReservaPublica::class)->ejecutar($data);

    expect($resultado['reserva']->estado)->toBe(EstadoReserva::CANCELADA);
    expect($resultado['reembolso_pendiente_administracion'])->toBeBool();
    expect($resultado['intentos_stripe'])->toBeInt();
});
