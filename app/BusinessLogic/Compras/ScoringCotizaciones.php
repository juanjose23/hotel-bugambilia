<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use App\Repository\Models\Compras\Cotizacion;
use Illuminate\Support\Collection;

final class ScoringCotizaciones
{
    /**
     * @param  Collection<int, Cotizacion>  $cotizaciones
     */
    public function calcularGanadora(Collection $cotizaciones): ?int
    {
        if ($cotizaciones->count() < 2) {
            return null;
        }

        $minTotalRef = $this->minRef($cotizaciones, 'total');
        $minDiasRef = $this->minRef($cotizaciones, 'dias_entrega');

        $mejorScore = -1.0;
        $ganadoraId = null;

        foreach ($cotizaciones as $cot) {
            $total = max((float) $cot->total, 1.0);
            $dias = max((float) $cot->dias_entrega, 1.0);

            $score = ($minTotalRef / $total) * 60 + ($minDiasRef / $dias) * 40;

            if ($score > $mejorScore) {
                $mejorScore = $score;
                $ganadoraId = (int) $cot->id;
            }
        }

        return $ganadoraId;
    }

    /**
     * @param  Collection<int, Cotizacion>  $cotizaciones
     * @return Collection<int, array{cotizacion_id: int, score_precio: float, score_tiempo: float, score_total: float}>
     */
    public function calcularDetallado(Collection $cotizaciones): Collection
    {
        if ($cotizaciones->isEmpty()) {
            return collect();
        }

        $minTotalRef = $this->minRef($cotizaciones, 'total');
        $minDiasRef = $this->minRef($cotizaciones, 'dias_entrega');

        /** @var Collection<int, array{cotizacion_id: int, score_precio: float, score_tiempo: float, score_total: float}> $result */
        $result = $cotizaciones->map(function ($cot) use ($minTotalRef, $minDiasRef) {
            $total = max((float) $cot->total, 1.0);
            $dias = max((float) $cot->dias_entrega, 1.0);
            $scorePrecio = round(($minTotalRef / $total) * 60, 2);
            $scoreTiempo = round(($minDiasRef / $dias) * 40, 2);

            return [
                'cotizacion_id' => (int) $cot->id,
                'score_precio' => $scorePrecio,
                'score_tiempo' => $scoreTiempo,
                'score_total' => round($scorePrecio + $scoreTiempo, 2),
            ];
        });

        return $result;
    }

    /**
     * @param  Collection<int, Cotizacion>  $cotizaciones
     */
    private function minRef(Collection $cotizaciones, string $campo): float
    {
        $raw = $cotizaciones->min($campo);
        $val = is_numeric($raw) ? (float) $raw : 0.0;

        return $val > 0 ? $val : 1.0;
    }
}
