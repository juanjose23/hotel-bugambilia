<?php

declare(strict_types=1);

namespace App\BusinessLogic\CheckIn;

final class NormalizarDatosClienteCheckIn
{
    private const array CAMPOS_CLIENTE = ['documento', 'telefono', 'email'];

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function extraerDatosCliente(array $datos): array
    {
        return array_intersect_key($datos, array_flip(self::CAMPOS_CLIENTE));
    }

    public function normalizarCantidadLlaves(mixed $llaves): int
    {
        if (is_numeric($llaves) && (int) $llaves > 0) {
            return (int) $llaves;
        }

        return 1;
    }
}
