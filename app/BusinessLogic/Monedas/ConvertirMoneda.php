<?php

declare(strict_types=1);

namespace App\BusinessLogic\Monedas;

use App\Repository\Queries\Monedas\ObtenerMonedaPorIdQuery;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;

/**
 * Regla de negocio: convierte un monto desde una moneda dada a la moneda
 * base del sistema (NIO), resolviendo la tasa de cambio vigente.
 */
final readonly class ConvertirMoneda
{
    public const CODIGO_BASE = 'NIO';

    public function __construct(
        private ObtenerMonedaPorIdQuery $monedaPorId,
        private ObtenerTasaCambioQuery $tasaCambioQuery,
    ) {}

    public function aBase(float $monto, ?int $monedaId): float
    {
        if ($monedaId === null) {
            return $monto;
        }

        $moneda = $this->monedaPorId->ejecutar($monedaId);
        $codigo = $moneda !== null ? $moneda->codigo : self::CODIGO_BASE;

        if (strtoupper($codigo) === self::CODIGO_BASE) {
            return $monto;
        }

        return $this->calcularConversion($monto, $codigo, self::CODIGO_BASE, now()->toDateString());
    }

    public function desdeBase(float $monto, ?int $monedaId): float
    {
        if ($monedaId === null) {
            return round($monto, 2);
        }

        $moneda = $this->monedaPorId->ejecutar($monedaId);
        $codigo = $moneda->codigo ?? self::CODIGO_BASE;

        if (strtoupper($codigo) === self::CODIGO_BASE) {
            return round($monto, 2);
        }

        return round($this->calcularConversion(
            $monto,
            self::CODIGO_BASE,
            strtoupper($codigo),
            now()->toDateString(),
        ), 2);
    }

    public function entre(float $monto, ?int $monedaOrigenId, ?int $monedaDestinoId): float
    {
        if ($monedaOrigenId === $monedaDestinoId) {
            return round($monto, 2);
        }

        return $this->desdeBase($this->aBase($monto, $monedaOrigenId), $monedaDestinoId);
    }

    private function calcularConversion(float $monto, string $origenCodigo, string $destinoCodigo, \DateTimeInterface|string $fecha): float
    {
        if ($origenCodigo === $destinoCodigo) {
            return $monto;
        }

        $tasa = $this->tasaCambioQuery->ejecutar($fecha, $origenCodigo, $destinoCodigo);

        if ($tasa > 0.0 && $tasa !== 1.0) {
            return round($monto * $tasa, 2);
        }

        $tasaInversa = $this->tasaCambioQuery->ejecutar($fecha, $destinoCodigo, $origenCodigo);

        if ($tasaInversa > 0.0) {
            return round($monto / $tasaInversa, 2);
        }

        return $monto;
    }
}
