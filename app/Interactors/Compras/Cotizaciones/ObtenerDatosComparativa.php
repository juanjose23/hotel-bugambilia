<?php

declare(strict_types=1);

namespace App\Interactors\Compras\Cotizaciones;

use App\BusinessLogic\Compras\ResolverEstrategiaCompra;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Queries\Compras\Cotizaciones\ObtenerCotizacionesParaComparativa;

final class ObtenerDatosComparativa
{
    public function __construct(
        private readonly ObtenerCotizacionesParaComparativa $query,
        private readonly ResolverEstrategiaCompra $resolverEstrategia,
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
            $solicitud->cotizaciones,
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
        $items = $solicitud->items;
        $cotizaciones = $solicitud->cotizaciones;

        $rows = [];
        foreach ($items as $sItem) {
            $itemData = [
                'producto_id' => $sItem->producto_id,
                'producto' => $sItem->producto->nombre ?? '',
                'variante_solicitada' => $sItem->variante->nombre_variante ?? 'Estándar',
                'cantidad' => $sItem->cantidad_aprobada > 0 ? $sItem->cantidad_aprobada : $sItem->cantidad_solicitada,
                'precios' => [],
                'variantes_ofrecidas' => [],
                'mejor_cotizacion_id' => null,
                'mejor_precio' => null,
                'ganador' => null,
            ];

            foreach ($cotizaciones as $cot) {
                $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                $precio = $cItem?->precio_unitario;

                $itemData['precios'][$cot->id] = $precio;
                $itemData['variantes_ofrecidas'][$cot->id] = $cItem?->variante->nombre_variante ?? ($cItem ? 'Estándar' : null);

                if ($precio !== null && ($itemData['mejor_precio'] === null || $precio < $itemData['mejor_precio'])) {
                    $itemData['mejor_precio'] = $precio;
                    $itemData['mejor_cotizacion_id'] = $cot->id;
                }

                if ($cItem?->es_elegido) {
                    $itemData['ganador'] = [
                        'cotizacion_id' => $cot->id,
                        'proveedor' => $this->getProveedorNombre($cot),
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

    public function getProveedorNombre(Cotizacion $cot): string
    {
        $proveedor = $cot->proveedor;
        if ($proveedor === null) {
            return "Proveedor #{$cot->proveedor_id}";
        }

        $persona = $proveedor->persona;
        if ($persona === null) {
            return "Proveedor #{$cot->proveedor_id}";
        }

        if ($persona->personaJuridica) {
            return $persona->personaJuridica->razon_social;
        }

        return $persona->primer_nombre ?? "Proveedor #{$cot->proveedor_id}";
    }
}
