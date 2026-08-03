<?php

declare(strict_types=1);

namespace App\BusinessLogic\Monedas;

use App\Repository\Queries\Monedas\ObtenerMonedaPorIdQuery;
use App\Services\Shared\TasaCambioService;

/**
 * Regla de negocio: convierte un monto desde una moneda dada a la moneda
 * base del sistema (NIO), resolviendo la tasa de cambio vigente.
 */
final class ConvertirMoneda
{
    public const CODIGO_BASE = 'NIO';

    public function __construct(
        private readonly ObtenerMonedaPorIdQuery $monedaPorId,
        private readonly TasaCambioService $tasaCambio,
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

        return $this->tasaCambio->convertir($monto, $codigo, self::CODIGO_BASE, now()->toDateString());
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

        return round($this->tasaCambio->convertir(
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
}
