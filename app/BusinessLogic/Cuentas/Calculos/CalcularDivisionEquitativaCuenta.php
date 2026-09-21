<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas\Calculos;

use DomainException;

final class CalcularDivisionEquitativaCuenta
{
    /**
     * Calcula una división equitativa en N partes del saldo de la cuenta con ajuste de centavos.
     *
     * @return array<int, array{parte: int, subtotal: float, monto_total: float}>
     */
    public function calcular(float $saldo, int $partes): array
    {
        if ($partes < 2) {
            throw new DomainException('La cantidad de separaciones debe ser al menos 2 partes.');
        }

        if ($saldo <= 0) {
            throw new DomainException('La cuenta no posee saldo pendiente para dividir.');
        }

        $montoBasePorParte = round($saldo / $partes, 2);
        $diferenciaCentavos = round($saldo - ($montoBasePorParte * $partes), 2);

        $resultado = [];
        for ($i = 1; $i <= $partes; $i++) {
            // Ajustar la última parte con cualquier sobrante por redondeo de centavos
            $montoParte = ($i === $partes) ? round($montoBasePorParte + $diferenciaCentavos, 2) : $montoBasePorParte;

            $resultado[] = [
                'parte' => $i,
                'subtotal' => round($montoParte, 2),
                'monto_total' => round($montoParte, 2),
            ];
        }

        return $resultado;
    }
}
