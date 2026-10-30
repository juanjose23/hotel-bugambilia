<?php

declare(strict_types=1);

namespace App\BusinessLogic\Espacios;

use DomainException;

final readonly class ValidarJerarquiaEspacio
{
    /** @param array<int, int|null> $jerarquia */
    public function validar(?int $id, ?int $padreId, array $jerarquia): void
    {
        $visitados = $id !== null ? [$id => true] : [];
        while ($padreId !== null) {
            if (isset($visitados[$padreId])) {
                throw new DomainException('El espacio no puede pertenecer a si mismo ni a uno de sus subespacios.');
            }
            if (! array_key_exists($padreId, $jerarquia)) {
                throw new DomainException('Seleccione un espacio padre existente y sin eliminar.');
            }
            $visitados[$padreId] = true;
            $padreId = $jerarquia[$padreId];
        }
    }
}
