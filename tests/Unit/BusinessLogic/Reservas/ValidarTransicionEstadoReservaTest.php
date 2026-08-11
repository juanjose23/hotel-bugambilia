<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\ValidarTransicionEstadoReserva;
use App\Enums\Reservas\EstadoReserva;

it('usa códigos enteros estables para los estados de reserva', function (): void {
    expect(EstadoReserva::PENDIENTE->value)->toBe(1)
        ->and(EstadoReserva::CONFIRMADA->value)->toBe(2)
        ->and(EstadoReserva::PARCIALMENTE_CHECKED_IN->value)->toBe(3)
        ->and(EstadoReserva::CHECKED_IN->value)->toBe(4)
        ->and(EstadoReserva::PARCIALMENTE_CHECKED_OUT->value)->toBe(5)
        ->and(EstadoReserva::CHECKED_OUT->value)->toBe(6)
        ->and(EstadoReserva::CANCELADA->value)->toBe(7)
        ->and(EstadoReserva::NO_SHOW->value)->toBe(8);
});

it('permite únicamente la secuencia operativa de una reserva', function (): void {
    $regla = new ValidarTransicionEstadoReserva;

    expect($regla->esPermitida(EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA))->toBeTrue()
        ->and($regla->esPermitida(EstadoReserva::CONFIRMADA, EstadoReserva::CHECKED_IN))->toBeTrue()
        ->and($regla->esPermitida(EstadoReserva::CHECKED_IN, EstadoReserva::CHECKED_OUT))->toBeTrue()
        ->and($regla->esPermitida(EstadoReserva::CHECKED_OUT, EstadoReserva::PENDIENTE))->toBeFalse()
        ->and($regla->esPermitida(EstadoReserva::CANCELADA, EstadoReserva::CONFIRMADA))->toBeFalse();
});

it('rechaza saltos de estado inválidos', function (): void {
    (new ValidarTransicionEstadoReserva)->validar(
        EstadoReserva::PENDIENTE,
        EstadoReserva::CHECKED_IN,
    );
})->throws(DomainException::class);
