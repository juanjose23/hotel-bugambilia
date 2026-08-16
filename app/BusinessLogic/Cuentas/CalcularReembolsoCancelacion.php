<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

final class CalcularReembolsoCancelacion
{
    public function ejecutar(float $totalPagado, float $montoPenalizacion): float
    {
        $pagado = max(0.0, $totalPagado);
        $penalizacion = max(0.0, $montoPenalizacion);

        return max(0.0, round($pagado - $penalizacion, 2));
    }
}
