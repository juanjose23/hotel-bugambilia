<?php

declare(strict_types=1);

use App\BusinessLogic\Promociones\ValidarPeriodoPromocion;

it('cuenta noches por fechas aunque el ingreso sea por la tarde', function (): void {
    (new ValidarPeriodoPromocion)->ejecutar(new DateTimeImmutable('2027-01-05 15:00'), new DateTimeImmutable('2027-01-07 11:00'), 2, 2, 2, 48, new DateTimeImmutable('2027-01-01'), true);
    expect(true)->toBeTrue();
});

it('rechaza condiciones de una oferta que no se cumplen', function (string $entrada, ?string $salida, int $personas, ?int $noches, string $mensaje): void {
    expect(fn () => (new ValidarPeriodoPromocion)->ejecutar(new DateTimeImmutable($entrada), $salida ? new DateTimeImmutable($salida) : null, $personas, $noches, 2, 48, new DateTimeImmutable('2027-01-01'), true))->toThrow(DomainException::class, $mensaje);
})->with([
    ['2027-01-05', '2027-01-07', 3, 2, 'personas'],
    ['2027-01-02', '2027-01-04', 2, 2, 'anticipación'],
    ['2027-01-05', '2027-01-08', 2, 2, 'noches'],
    ['2027-01-05', '2027-01-07', 2, null, 'configuradas'],
]);
