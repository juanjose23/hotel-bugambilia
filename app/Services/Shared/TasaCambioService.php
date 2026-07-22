<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Repository\Models\Monedas\TasaCambio;

final class TasaCambioService
{
    public function obtenerTasa(\DateTimeInterface|string $fecha, string $origenCodigo = 'USD', string $destinoCodigo = 'NIO'): float
    {
        return TasaCambio::obtenerTasa($fecha, $origenCodigo, $destinoCodigo);
    }

    public function convertir(float $monto, string $origenCodigo, string $destinoCodigo, \DateTimeInterface|string $fecha): float
    {
        if ($origenCodigo === $destinoCodigo) {
            return $monto;
        }

        $tasa = TasaCambio::obtenerTasa($fecha, $origenCodigo, $destinoCodigo);

        if ($tasa > 0.0 && $tasa !== 1.0) {
            return round($monto * $tasa, 2);
        }

        $tasaInversa = TasaCambio::obtenerTasa($fecha, $destinoCodigo, $origenCodigo);

        if ($tasaInversa > 0.0) {
            return round($monto / $tasaInversa, 2);
        }

        return $monto;
    }
}
