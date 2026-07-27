<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\BusinessLogic\Compras\ResolverEstrategiaCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerCotizacionesParaComparativa;
use App\Repository\Queries\Shared\ObtenerNombrePersona;

final readonly class ObtenerDatosComparativa
{
    public function __construct(
        private ObtenerCotizacionesParaComparativa $query,
        private ResolverEstrategiaCompra $resolverEstrategia,
        private ObtenerNombrePersona $obtenerNombrePersona,
    ) {}

    /** @return array<string, mixed> */
    public function ejecutar(int $solicitudId): array
    {
        $solicitud = $this->query->ejecutar($solicitudId);

        if ($solicitud === null) {
            return [
                'solicitud' => null,
                'comparacion' => [],
                'recomendacion' => null,
            ];
        }

        $comparacion = $this->buildComparisonData($solicitud);
        $recomendacion = $this->resolverEstrategia->ejecutar(
            $solicitud,
            $solicitud->getAttribute('cotizaciones'),
        );

        return [
            'solicitud' => $solicitud,
            'comparacion' => $comparacion,
            'recomendacion' => $recomendacion,
        ];
    }

    /** @return array<string, mixed> */
    public function buildComparisonData(Solicitud $solicitud): array
    {
        $items = $solicitud->getAttribute('items');
        $cotizaciones = $solicitud->getAttribute('cotizaciones');

        $rows = [];
        foreach ($items as $sItem) {
            $cantidadAprobada = (float) $sItem->getAttribute('cantidad_aprobada');
            $cantidadSolicitada = (float) $sItem->getAttribute('cantidad_solicitada');
            $producto = $sItem->relationLoaded('producto') ? $sItem->getRelation('producto') : null;
            $variante = $sItem->relationLoaded('variante') ? $sItem->getRelation('variante') : null;

            $itemData = [
                'producto_id' => $sItem->getAttribute('producto_id'),
                'producto' => $producto?->getAttribute('nombre') ?? '',
                'variante_solicitada' => $variante?->getAttribute('nombre_variante') ?? 'Estándar',
                'cantidad' => $cantidadAprobada > 0 ? $cantidadAprobada : $cantidadSolicitada,
                'cantidad_solicitada' => $cantidadSolicitada,
                'cantidad_aprobada' => $cantidadAprobada,
                'precios' => [],
                'variantes_ofrecidas' => [],
                'mejor_cotizacion_id' => null,
                'mejor_precio' => null,
                'ganador' => null,
            ];

            foreach ($cotizaciones as $cot) {
                $cotItems = $cot->relationLoaded('items') ? $cot->getRelation('items') : collect();
                $cItem = $cotItems->where('producto_id', $sItem->getAttribute('producto_id'))->first();
                $precio = $cItem?->getAttribute('precio_unitario');

                $cItemVariante = $cItem && $cItem->relationLoaded('variante') ? $cItem->getRelation('variante') : null;

                $cotId = $cot->getAttribute('id');
                $itemData['precios'][$cotId] = $precio;
                $itemData['variantes_ofrecidas'][$cotId] = $cItemVariante?->getAttribute('nombre_variante') ?? ($cItem ? 'Estándar' : null);

                if ($precio !== null && ($itemData['mejor_precio'] === null || $precio < $itemData['mejor_precio'])) {
                    $itemData['mejor_precio'] = $precio;
                    $itemData['mejor_cotizacion_id'] = $cotId;
                }

                if ($cItem?->getAttribute('es_elegido')) {
                    $itemData['ganador'] = [
                        'cotizacion_id' => $cotId,
                        'proveedor' => $this->obtenerNombrePersona->ejecutar($cot->proveedor->persona),
                        'precio' => $precio,
                    ];
                }
            }
            $rows[] = $itemData;
        }

        return [
            'cotizaciones' => $cotizaciones,
            'rows' => $rows,
        ];
    }
}
