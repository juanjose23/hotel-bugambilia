<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\Interactors\Inventario\TrasladarEntreBodegas;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Queries\Catalogos\BuscarVariantePorId;
use App\Repository\Queries\Inventario\Stock\ObtenerStockParaConsumo;

final readonly class ReabastecedorFefo
{
    public function __construct(
        private TrasladarEntreBodegas $trasladarEntreBodegas,
        private ObtenerStockParaConsumo $obtenerStockParaConsumo,
        private BuscarVariantePorId $buscarVariantePorId,
    ) {}

    /**
     * @param  array<int, array{producto_variante_id?: int|null, producto_id?: int|null, cantidad: float|int|string, lote_id?: int|null}>  $items
     */
    public function reabastecer(int $bodegaOrigenId, int $carritoDestinoId, array $items, ?int $creadoPorId = null): void
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Debe agregar al menos un insumo para reabastecer.');
        }

        foreach ($items as $item) {
            $varianteId = isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null;
            $productoId = isset($item['producto_id']) ? (int) $item['producto_id'] : null;
            $cantidadRequerida = (float) $item['cantidad'];
            $loteId = isset($item['lote_id']) ? (int) $item['lote_id'] : null;

            if ($cantidadRequerida <= 0) {
                continue;
            }

            if ($varianteId !== null) {
                $variante = $this->buscarVariantePorId->ejecutar($varianteId);
                if (! $variante) {
                    throw new \RuntimeException("No se encontró la variante con ID {$varianteId}");
                }
                $productoId = $variante->producto_id;
            } elseif ($productoId === null) {
                throw new \InvalidArgumentException('Debe especificar producto_id o producto_variante_id.');
            }

            if ($loteId !== null) {
                $this->trasladarEntreBodegas->execute(
                    productoId: $productoId,
                    loteId: $loteId,
                    cantidad: $cantidadRequerida,
                    origenId: $bodegaOrigenId,
                    destinoId: $carritoDestinoId,
                    productoVarianteId: $varianteId,
                    creadoPorId: $creadoPorId,
                    referencia: "Abastecimiento de Carrito #{$carritoDestinoId}",
                    notas: 'Traslado directo de insumo especificado.'
                );
            } else {
                $this->reabastecerPorFefo($bodegaOrigenId, $carritoDestinoId, $productoId, $varianteId, $cantidadRequerida, $creadoPorId);
            }
        }
    }

    private function reabastecerPorFefo(int $bodegaOrigenId, int $carritoDestinoId, int $productoId, ?int $varianteId, float $cantidadRequerida, ?int $creadoPorId): void
    {
        $stocks = $this->obtenerStockParaConsumo->ejecutar(
            productoId: $productoId,
            ubicacionId: $bodegaOrigenId,
            productoVarianteId: $varianteId
        );

        $ordenados = $stocks->sortBy(function (Stock $st) {
            return $st->lote?->fecha_vencimiento?->format('Y-m-d') ?? '9999-12-31';
        })->values();

        $totalDisponible = (float) $ordenados->sum(fn (Stock $st) => (float) $st->cantidad);
        if ($totalDisponible < $cantidadRequerida) {
            throw new \RuntimeException(sprintf(
                'Stock insuficiente en la bodega de origen. Disponible: %f, Requerido: %f',
                $totalDisponible,
                $cantidadRequerida
            ));
        }

        $restante = $cantidadRequerida;
        /** @var Stock $stock */
        foreach ($ordenados as $stock) {
            if ($restante <= 0.0) {
                break;
            }

            $aTrasladar = min((float) $stock->cantidad, $restante);
            $this->trasladarEntreBodegas->execute(
                productoId: $productoId,
                loteId: (int) $stock->lote_id,
                cantidad: $aTrasladar,
                origenId: $bodegaOrigenId,
                destinoId: $carritoDestinoId,
                productoVarianteId: $varianteId,
                creadoPorId: $creadoPorId,
                referencia: "Abastecimiento de Carrito #{$carritoDestinoId}",
                notas: 'Traslado FEFO automático.'
            );

            $restante -= $aTrasladar;
        }
    }
}
