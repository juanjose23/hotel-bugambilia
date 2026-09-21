<?php

declare(strict_types=1);

namespace App\BusinessLogic\Shared\Support;

use InvalidArgumentException;

final readonly class LeerDatoPayload
{
    /** @param array<array-key, mixed> $datos */
    public function enteroRequerido(array $datos, string $campo): int
    {
        $valor = $datos[$campo] ?? null;

        if (is_int($valor)) {
            return $valor;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            return (int) $valor;
        }

        throw new InvalidArgumentException("El campo {$campo} no es válido.");
    }

    /** @param array<array-key, mixed> $datos */
    public function enteroOpcional(array $datos, string $campo, int $predeterminado): int
    {
        $valor = $datos[$campo] ?? $predeterminado;

        return is_numeric($valor) ? (int) $valor : $predeterminado;
    }

    /** @param array<array-key, mixed> $datos */
    public function cadenaRequerida(array $datos, string $campo): string
    {
        $valor = $datos[$campo] ?? null;

        if (is_string($valor) && trim($valor) !== '') {
            return trim($valor);
        }

        throw new InvalidArgumentException("El campo {$campo} es obligatorio.");
    }

    /** @param array<array-key, mixed> $datos */
    public function cadenaOpcional(array $datos, string $campo, ?string $predeterminado = null): ?string
    {
        $valor = $datos[$campo] ?? null;

        if (is_string($valor) && trim($valor) !== '') {
            return trim($valor);
        }

        return $predeterminado;
    }

    /**
     * @param  array<array-key, mixed>  $datos
     * @param  array<int, mixed>  $predeterminado
     * @return array<int, mixed>
     */
    public function arreglo(array $datos, string $campo, array $predeterminado = []): array
    {
        $valor = $datos[$campo] ?? null;

        return is_array($valor) ? array_values($valor) : $predeterminado;
    }
}
