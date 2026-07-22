<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\BusinessLogic\Compras\VerificarSolicitudBloqueada;
use App\Repository\Models\Compras\Solicitud;
use DomainException;

final class AplicarRecomendacionLogistica
{
    public function __construct(
        private readonly ElegirCotizacionGanadora $elegirCotizacion,
        private readonly SeleccionarItemGanador $seleccionarItem,
        private readonly VerificarSolicitudBloqueada $verificarBloqueo,
    ) {}

    /** @param array<string, mixed> $recomendacion */
    public function ejecutar(Solicitud $solicitud, array $recomendacion): void
    {
        if ($this->verificarBloqueo->estaBloqueada($solicitud)) {
            throw new DomainException('No se puede aplicar la recomendación porque la solicitud tiene órdenes activas.');
        }

        $tipo = $recomendacion['tipo'] ?? '';

        if ($tipo === 'PROVEEDOR ÚNICO') {
            $recomendacionId = $recomendacion['cotizacion_id'] ?? null;
            $cotizacionId = is_numeric($recomendacionId) ? (int) $recomendacionId : 0;
            $this->elegirCotizacion->ejecutar($cotizacionId);
        } else {
            foreach ($solicitud->items as $sItem) {
                $mejorPrecio = null;
                $mejorCotId = null;

                foreach ($solicitud->cotizaciones as $cot) {
                    $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                    if ($cItem !== null && ($mejorPrecio === null || $cItem->precio_unitario < $mejorPrecio)) {
                        $mejorPrecio = $cItem->precio_unitario;
                        $mejorCotId = $cot->id;
                    }
                }

                if ($mejorCotId !== null) {
                    $this->seleccionarItem->ejecutar($mejorCotId, $sItem->producto_id);
                }
            }
        }
    }
}
