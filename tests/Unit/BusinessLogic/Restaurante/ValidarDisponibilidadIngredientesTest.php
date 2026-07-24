<?php

declare(strict_types=1);

use App\BusinessLogic\Restaurante\ValidarDisponibilidadIngredientes;

it('permite consumir cuando existe stock suficiente', function (): void {
    app(ValidarDisponibilidadIngredientes::class)->validar(2.5, 3.0, 'Arroz');

    expect(true)->toBeTrue();
});

it('rechaza un consumo que dejaría stock negativo', function (): void {
    app(ValidarDisponibilidadIngredientes::class)->validar(4.0, 3.0, 'Arroz');
})->throws(DomainException::class, 'Stock insuficiente para Arroz');
