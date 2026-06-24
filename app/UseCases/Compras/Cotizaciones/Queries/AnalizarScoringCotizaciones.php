<?php

namespace App\UseCases\Compras\Cotizaciones\Queries;

use App\Models\Compras\Solicitud;
use Illuminate\Support\Collection;

class AnalizarScoringCotizaciones
{
    /**
     * @return int|null ID de la cotización ganadora por score
     */
    public function execute(Solicitud $solicitud): ?int
    {
        $solicitud->loadMissing('cotizaciones');
        $cotizaciones = $solicitud->cotizaciones;

        if ($cotizaciones->count() < 2) {
            return null;
        }

        $minTotalRaw = $cotizaciones->min('total');
        $minDiasRaw = $cotizaciones->min('dias_entrega');
        $minTotal = is_numeric($minTotalRaw) ? (float) $minTotalRaw : 0.0;
        $minDias = is_numeric($minDiasRaw) ? (float) $minDiasRaw : 0.0;

        $minTotalRef = $minTotal > 0 ? $minTotal : 1.0;
        $minDiasRef = $minDias > 0 ? $minDias : 1.0;

        $mejorScore = -1.0;
        $ganadoraId = null;

        foreach ($cotizaciones as $cot) {
            $rawTotal = $cot->total > 0 ? $cot->total : 1;
            $rawDias = $cot->dias_entrega > 0 ? $cot->dias_entrega : 1;
            $total = (float) $rawTotal;
            $dias = (float) $rawDias;

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

    /** @return Collection<int, array{cotizacion_id: mixed, score_precio: float, score_tiempo: float, score_total: float}> */
    public function getDetailedScoring(Solicitud $solicitud): Collection
    {
        $solicitud->loadMissing('cotizaciones');
        $cotizaciones = $solicitud->cotizaciones;

        if ($cotizaciones->isEmpty()) {
            return collect();
        }

        $minTotalRaw2 = $cotizaciones->min('total');
        $minDiasRaw2 = $cotizaciones->min('dias_entrega');
        $minTotal = is_numeric($minTotalRaw2) ? (float) $minTotalRaw2 : 0.0;
        $minDias = is_numeric($minDiasRaw2) ? (float) $minDiasRaw2 : 0.0;
        $minTotalRef = $minTotal > 0 ? $minTotal : 1.0;
        $minDiasRef = $minDias > 0 ? $minDias : 1.0;

        /** @var Collection<int, array{cotizacion_id: mixed, score_precio: float, score_tiempo: float, score_total: float}> $result */
        $result = $cotizaciones->map(function ($cot) use ($minTotalRef, $minDiasRef) {
            $rawTotal = $cot->total > 0 ? $cot->total : 1;
            $rawDias = $cot->dias_entrega > 0 ? $cot->dias_entrega : 1;
            $total = (float) $rawTotal;
            $dias = (float) $rawDias;

            $scorePrecio = ($minTotalRef / $total) * 60;
            $scoreTiempo = ($minDiasRef / $dias) * 40;

            return [
                'cotizacion_id' => $cot->id,
                'score_precio' => round($scorePrecio, 2),
                'score_tiempo' => round($scoreTiempo, 2),
                'score_total' => round($scorePrecio + $scoreTiempo, 2),
            ];
        });

        return $result;
    }
}
