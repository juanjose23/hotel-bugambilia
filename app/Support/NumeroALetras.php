<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class NumeroALetras
{
    private const UNIDADES = [
        '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE',
        'DIECIOCHO', 'DIECINUEVE', 'VEINTE', 'VEINTIÚN', 'VEINTIDÓS', 'VEINTITRÉS',
        'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE',
    ];

    private const DECENAS = [
        '', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA',
    ];

    private const CENTENAS = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS',
    ];

    public static function convertir(
        float $numero,
        string $monedaPlural = 'CÓRDOBAS',
        ?string $monedaSingular = null
    ): string {
        if (! is_finite($numero)) {
            throw new InvalidArgumentException('El número debe ser finito.');
        }

        $monedaSingular ??= $monedaPlural;

        $centavosTotales = (int) round($numero * 100);
        $signo = $centavosTotales < 0 ? 'MENOS ' : '';
        $centavosTotales = abs($centavosTotales);

        $entero = intdiv($centavosTotales, 100);
        $centavos = $centavosTotales % 100;

        $textoEntero = self::convertirEntero($entero);

        $moneda = $entero === 1 ? $monedaSingular : $monedaPlural;

        $de = '';
        if ($entero >= 1000000 && $entero % 1000000 === 0) {
            $de = ' DE';
        }

        $centavosStr = str_pad((string) $centavos, 2, '0', STR_PAD_LEFT);

        return trim("{$signo}{$textoEntero}{$de} {$moneda} CON {$centavosStr}/100");
    }

    private static function convertirEntero(int $n): string
    {
        if ($n === 0) {
            return 'CERO';
        }

        $resultado = '';

        if ($n >= 1000000000000) {
            $billones = intdiv($n, 1000000000000);
            $n %= 1000000000000;

            if ($billones === 1) {
                $resultado .= 'UN BILLÓN ';
            } else {
                $resultado .= self::convertirEntero($billones).' BILLONES ';
            }
        }

        if ($n >= 1000000) {
            $millones = intdiv($n, 1000000);
            $n %= 1000000;

            if ($millones === 1) {
                $resultado .= 'UN MILLÓN ';
            } else {
                $resultado .= self::convertirEntero($millones).' MILLONES ';
            }
        }

        if ($n >= 1000) {
            $miles = intdiv($n, 1000);
            $n %= 1000;

            if ($miles === 1) {
                $resultado .= 'MIL ';
            } else {
                $resultado .= self::convertirEntero($miles).' MIL ';
            }
        }

        if ($n >= 100) {
            if ($n === 100) {
                $resultado .= 'CIEN ';
                $n = 0;
            } else {
                $centena = intdiv($n, 100);
                $resultado .= self::CENTENAS[$centena].' ';
                $n %= 100;
            }
        }

        if ($n > 0) {
            if ($n < 30) {
                $resultado .= self::UNIDADES[$n].' ';
            } else {
                $decena = intdiv($n, 10);
                $unidad = $n % 10;

                $resultado .= self::DECENAS[$decena];

                if ($unidad > 0) {
                    $resultado .= ' Y '.self::UNIDADES[$unidad];
                }

                $resultado .= ' ';
            }
        }

        return trim($resultado);
    }
}
