<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

/**
 * Calcula el vuelto a entregar en un cobro cuando el monto pagado supera
 * el monto a cobrar, convirtiendo la diferencia a la moneda del vuelto.
 */
final class CalcularVuelto
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{vuelto: float, texto: string}|null
     */
    public function ejecutar(
        float $saldoCuenta,
        string $codigoMonedaPago,
        string $codigoMonedaVuelto,
        string $simboloMonedaVuelto,
        float $tasaConversion,
        array $data,
    ): ?array {
        $montoCobrar = is_numeric($data['monto'] ?? null) ? (float) $data['monto'] : 0.0;
        $montoRecibido = is_numeric($data['monto_recibido'] ?? null) ? (float) $data['monto_recibido'] : 0.0;

        $pagaCon = $montoRecibido > 0 ? $montoRecibido : $montoCobrar;
        $montoBaseCobro = $saldoCuenta > 0 ? min($montoCobrar, $saldoCuenta) : $montoCobrar;

        if ($pagaCon <= $montoBaseCobro) {
            return null;
        }

        $diferencia = $pagaCon - $montoBaseCobro;

        if ($codigoMonedaPago === $codigoMonedaVuelto) {
            $vueltoFinal = $diferencia;
        } elseif ($codigoMonedaPago === 'USD' && $codigoMonedaVuelto !== 'USD') {
            $vueltoFinal = $diferencia * ($tasaConversion > 0 ? $tasaConversion : 1);
        } else {
            $vueltoFinal = $tasaConversion > 0 ? $diferencia / $tasaConversion : $diferencia;
        }

        return [
            'vuelto' => round($vueltoFinal, 2),
            'texto' => "Vuelto entregado: {$simboloMonedaVuelto} ".number_format($vueltoFinal, 2)." ({$codigoMonedaVuelto})",
        ];
    }
}
