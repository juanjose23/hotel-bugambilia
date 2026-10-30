<?php

declare(strict_types=1);

namespace App\BusinessLogic\Tesoreria;

use DomainException;

final class CalcularArqueo
{
    public function centavos(float $importe): int
    {
        if (! is_finite($importe) || $importe < 0 || $importe > 99999999999.99 || abs($importe * 100 - round($importe * 100)) > .00001) {
            throw new DomainException('Indique un importe válido, con un máximo de dos decimales.');
        }

        return (int) round($importe * 100);
    }

    /** @return array{esperado: float, contado: float, diferencia: float} */
    public function comparar(float $esperado, float $contado): array
    {
        $a = $this->centavos($esperado);
        $b = $this->centavos($contado);

        return ['esperado' => round($a / 100, 2), 'contado' => round($b / 100, 2), 'diferencia' => round(($b - $a) / 100, 2)];
    }
}
