<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Solicitud;
use Illuminate\Support\Collection;

/**
 * Caso de Uso: Análisis de Scoring Multicriterio para Cotizaciones
 *
 * Evalúa las cotizaciones de una solicitud basándose en una ponderación
 * de Precio (60%) y Tiempo de Entrega (40%) para recomendar la opción más equilibrada.
 */
class AnalizarScoringCotizaciones
{
    /**
     * Ejecuta el análisis de scoring y retorna el ID de la cotización recomendada.
     *
     * @return int|null ID de la cotización ganadora por score
     */
    public function execute(Solicitud $solicitud): ?int
    {
        $cotizaciones = $solicitud->cotizaciones()->get();

        if ($cotizaciones->count() < 2) {
            return null;
        }

        $minTotal = $cotizaciones->min('total');
        $minDias = $cotizaciones->min('dias_entrega');

        // Evitar valores de referencia cero para divisiones
        $minTotalRef = $minTotal > 0 ? $minTotal : 1;
        $minDiasRef = $minDias > 0 ? $minDias : 1;

        $mejorScore = -1;
        $ganadoraId = null;

        foreach ($cotizaciones as $cot) {
            // Evitar división por cero en la cotización actual
            $total = $cot->total > 0 ? $cot->total : 1;
            $dias = $cot->dias_entrega > 0 ? $cot->dias_entrega : 1;

            /**
             * Scoring Algorithm:
             * - Menor precio y menor tiempo es mejor.
             * - Invertimos los valores para que el menor sea el puntaje más alto.
             * - Ponderación: 60% Precio, 40% Tiempo.
             */
            $scorePrecio = ($minTotalRef / $total) * 60;
            $scoreTiempo = ($minDiasRef / $dias) * 40;

            $scoreFinal = $scorePrecio + $scoreTiempo;

            if ($scoreFinal > $mejorScore) {
                $mejorScore = $scoreFinal;
                $ganadoraId = $cot->id;
            }
        }

        return $ganadoraId;
    }

    /**
     * Retorna el detalle del scoring para todas las cotizaciones (útil para la UI)
     */
    /** @return Collection<int, array{cotizacion_id: mixed, score_precio: float, score_tiempo: float, score_total: float}> */
    public function getDetailedScoring(Solicitud $solicitud): Collection
    {
        $cotizaciones = $solicitud->cotizaciones()->get();

        if ($cotizaciones->isEmpty()) {
            return collect();
        }

        $minTotal = $cotizaciones->min('total');
        $minDias = $cotizaciones->min('dias_entrega');
        $minTotalRef = $minTotal > 0 ? $minTotal : 1;
        $minDiasRef = $minDias > 0 ? $minDias : 1;

        return $cotizaciones->map(function ($cot) use ($minTotalRef, $minDiasRef) {
            $total = $cot->total > 0 ? $cot->total : 1;
            $dias = $cot->dias_entrega > 0 ? $cot->dias_entrega : 1;

            $scorePrecio = ($minTotalRef / $total) * 60;
            $scoreTiempo = ($minDiasRef / $dias) * 40;

            return [
                'cotizacion_id' => $cot->id,
                'score_precio' => round($scorePrecio, 2),
                'score_tiempo' => round($scoreTiempo, 2),
                'score_total' => round($scorePrecio + $scoreTiempo, 2),
            ];
        });
    }
}
