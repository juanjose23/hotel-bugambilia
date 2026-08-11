<?php

declare(strict_types=1);

namespace App\Support;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;

final class MonedaHelper
{
    private static ?Moneda $monedaPredeterminada = null;

    public static function obtenerMonedaPredeterminada(): ?Moneda
    {
        if (self::$monedaPredeterminada === null) {
            self::$monedaPredeterminada = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar();
        }

        return self::$monedaPredeterminada;
    }

    public static function codigo(?Moneda $moneda = null): string
    {
        if ($moneda !== null) {
            return $moneda->codigo;
        }

        return self::obtenerMonedaPredeterminada()->codigo ?? 'USD';
    }

    public static function simbolo(?Moneda $moneda = null): string
    {
        if ($moneda !== null) {
            return $moneda->simbolo;
        }

        return self::obtenerMonedaPredeterminada()->simbolo ?? '$';
    }

    public static function formatear(?float $monto, ?Moneda $moneda = null): string
    {
        $simbolo = self::simbolo($moneda);
        $montoFormateado = number_format($monto ?? 0.0, 2);

        return "{$simbolo} {$montoFormateado}";
    }

    public static function resetCache(): void
    {
        self::$monedaPredeterminada = null;
    }
}
