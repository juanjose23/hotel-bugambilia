<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\ParsearPayloadReserva;
use App\Enums\Reservas\TipoReserva;

test('parsea payload de reserva valido', function (): void {
    $parser = new ParsearPayloadReserva;

    $datos = [
        'tipo_reserva' => 'habitacion',
        'fecha_check_in' => '2026-10-01',
        'fecha_check_out' => '2026-10-05',
        'hora_reserva' => ' 15:00 ',
        'items_preorden' => [['plato_id' => 1, 'cantidad' => 2]],
    ];

    $resultado = $parser->parsear($datos);

    expect($resultado['tipo'])->toBe(TipoReserva::HABITACION);
    expect($resultado['checkIn']->format('Y-m-d'))->toBe('2026-10-01');
    expect($resultado['checkOut']?->format('Y-m-d'))->toBe('2026-10-05');
    expect($resultado['horaReserva'])->toBe('15:00');
    expect($resultado['itemsPreorden'])->toBe([['plato_id' => 1, 'cantidad' => 2]]);
});

test('lanza excepcion si tipo_reserva o fecha_check_in son invalidos', function (): void {
    $parser = new ParsearPayloadReserva;

    $parser->parsear(['fecha_check_in' => '2026-10-01']);
})->throws(InvalidArgumentException::class, 'Los datos de la reserva no son válidos.');
