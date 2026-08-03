<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cobro;

use App\Repository\Models\Monedas\Moneda;
use App\Repository\Queries\Monedas\ObtenerMonedaPorIdQuery;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;

final class ResolverMonedaCobro
{
    public function __construct(
        private readonly ObtenerMonedaPorIdQuery $monedaPorId,
        private readonly ObtenerMonedaPredeterminadaQuery $obtenerPredeterminada,
        private readonly ObtenerTasaCambioQuery $tasaCambio,
    ) {}

    public function obtenerSimbolo(Moneda $moneda): string
    {
        return $moneda->simbolo ?? 'C$';
    }

    /**
     * Resuelve la tasa de cambio entre dos monedas.
     * Si la moneda origen es NIO, retorna 1.0.
     */
    public function resolverTasaCambio(string $desdeCodigo, string $hastaCodigo): float
    {
        if (strtoupper($desdeCodigo) === 'NIO') {
            return 1.0;
        }

        try {
            return $this->tasaCambio->ejecutar(
                now()->toDateString(),
                $desdeCodigo,
                $hastaCodigo,
            );
        } catch (\Throwable) {
            return 1.0;
        }
    }

    /**
     * Resuelve moneda desde un ID, usando la predeterminada como fallback.
     */
    public function resolverMoneda(?int $monedaId): Moneda
    {
        if ($monedaId !== null) {
            $moneda = $this->monedaPorId->ejecutar($monedaId);

            if ($moneda instanceof Moneda) {
                return $moneda;
            }
        }

        return $this->obtenerPredeterminada->ejecutar()
            ?? new Moneda;
    }
}
