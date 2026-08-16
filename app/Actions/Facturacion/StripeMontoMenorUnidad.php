<?php

declare(strict_types=1);

namespace App\Actions\Facturacion;

final readonly class StripeMontoMenorUnidad
{
    /**
     * Monedas de la ISO 4217 sin decimales (sin menor unidad).
     *
     * @var list<string>
     */
    private const MONEDAS_SIN_DECIMALES = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

    public function ejecutar(float $monto, string $moneda): int
    {
        return in_array(mb_strtoupper($moneda), self::MONEDAS_SIN_DECIMALES, true)
            ? (int) round($monto)
            : (int) round($monto * 100);
    }
}
