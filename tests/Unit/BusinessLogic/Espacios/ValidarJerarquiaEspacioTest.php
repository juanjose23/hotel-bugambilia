<?php

declare(strict_types=1);

use App\BusinessLogic\Espacios\ValidarJerarquiaEspacio;

test('acepta una jerarquia de restaurante salon y mesa sin ciclos', function (): void {
    (new ValidarJerarquiaEspacio)->validar(4, 3, [1 => null, 2 => 1, 3 => 2]);
    expect(true)->toBeTrue();
});

test('rechaza ciclos de espacios y padres inexistentes', function (?int $id, ?int $padre, array $map): void {
    expect(fn () => (new ValidarJerarquiaEspacio)->validar($id, $padre, $map))->toThrow(DomainException::class);
})->with([
    'propio padre' => [1, 1, [1 => null]],
    'hijo como padre' => [1, 3, [1 => null, 2 => 1, 3 => 2]],
    'ciclo previo' => [4, 2, [2 => 3, 3 => 2]],
    'padre inexistente' => [4, 9, [1 => null]],
]);
