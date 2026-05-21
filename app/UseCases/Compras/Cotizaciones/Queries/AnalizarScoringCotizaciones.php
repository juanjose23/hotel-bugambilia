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

        $minTotal = $cotizaciones->min('total');
        $minDias = $cotizaciones->min('dias_entrega');

        $minTotalRef = $minTotal > 0 ? $minTotal : 1;
        $minDiasRef = $minDias > 0 ? $minDias : 1;

        $mejorScore = -1;
        $ganadoraId = null;

        foreach ($cotizaciones as $cot) {
            $total = $cot->total > 0 ? $cot->total : 1;
            $dias = $cot->dias_entrega > 0 ? $cot->dias_entrega : 1;

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
