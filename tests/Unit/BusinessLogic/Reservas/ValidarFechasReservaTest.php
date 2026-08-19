<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\ValidarFechasReserva;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow(now()->setTime(12, 0, 0));
});

test('lanza excepcion si la fecha de check in es pasada', function (): void {
    $validator = new ValidarFechasReserva;

    $checkInPasado = (new DateTimeImmutable('now'))->modify('-2 days');

    $validator->validar($checkInPasado, null);
})->throws(DomainException::class, 'No es posible realizar una reservación para fechas pasadas.');

test('lanza excepcion si es hoy pero la hora ya transcurrio', function (): void {
    $validator = new ValidarFechasReserva;

    $hoy = new DateTimeImmutable(now()->toDateString());
    $horaPasada = now()->subHours(2)->format('H:i');

    $validator->validar($hoy, $horaPasada);
})->throws(DomainException::class, 'No es posible realizar una reservación para una hora que ya ha transcurrido hoy.');

test('permite fecha de check in futura', function (): void {
    $validator = new ValidarFechasReserva;

    $checkInFuturo = (new DateTimeImmutable('now'))->modify('+2 days');

    $validator->validar($checkInFuturo, '14:00');

    expect(true)->toBeTrue();
});

test('permite hoy si la hora es futura', function (): void {
    $validator = new ValidarFechasReserva;

    $hoy = new DateTimeImmutable(now()->toDateString());
    $horaFutura = now()->addHours(2)->format('H:i');

    $validator->validar($hoy, $horaFutura);

    expect(true)->toBeTrue();
});
