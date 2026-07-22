<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\BusinessLogic\Compras\ScoringCotizaciones;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Support\Collection;

final class AnalizarScoringCotizaciones
{
    public function __construct(
        private readonly ScoringCotizaciones $scoring,
    ) {}

    public function ejecutar(Solicitud $solicitud): ?int
    {
        $solicitud->loadMissing('cotizaciones');

        return $this->scoring->calcularGanadora($solicitud->cotizaciones);
    }

    /**
     * @return Collection<int, array{cotizacion_id: int, score_precio: float, score_tiempo: float, score_total: float}>
     */
    public function ejecutarDetallado(Solicitud $solicitud): Collection
    {
        $solicitud->loadMissing('cotizaciones');

        return $this->scoring->calcularDetallado($solicitud->cotizaciones);
    }
}
