<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\Enums\Inventario\EstadoLote;
use App\Interactors\Inventario\TrasladarEntreBodegas;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class ReabastecedorFefo
{
    public function __construct(
        private readonly TrasladarEntreBodegas $trasladarEntreBodegas,
    ) {}

    /**
     * @param  array<int, array{producto_variante_id?: int|null, producto_id?: int|null, cantidad: float|int|string, lote_id?: int|null}>  $items
     */
    public function reabastecer(int $bodegaOrigenId, int $carritoDestinoId, array $items, ?int $creadoPorId = null): void
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Debe agregar al menos un insumo para reabastecer.');
        }

        DB::transaction(function () use ($bodegaOrigenId, $carritoDestinoId, $items, $creadoPorId) {
            foreach ($items as $item) {
                $varianteId = isset($item['producto_variante_id']) ? (int) $item['producto_variante_id'] : null;
                $productoId = isset($item['producto_id']) ? (int) $item['producto_id'] : null;
                $cantidadRequerida = (float) $item['cantidad'];
                $loteId = isset($item['lote_id']) ? (int) $item['lote_id'] : null;

                if ($cantidadRequerida <= 0) {
                    continue;
                }

                if ($varianteId !== null) {
                    $variante = ProductoVariante::findOrFail($varianteId);
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
        });
    }

    private function reabastecerPorFefo(int $bodegaOrigenId, int $carritoDestinoId, int $productoId, ?int $varianteId, float $cantidadRequerida, ?int $creadoPorId): void
    {
        $stocks = Stock::with(['lote'])
            ->where('producto_id', $productoId)
            ->when($varianteId !== null, fn ($q) => $q->where('producto_variante_id', $varianteId))
            ->when($varianteId === null, fn ($q) => $q->whereNull('producto_variante_id'))
            ->where('ubicacion_id', $bodegaOrigenId)
            ->where('cantidad', '>', 0)
            ->where(function ($q) {
                $q->whereNull('lote_id')
                    ->orWhereHas('lote', function ($sub) {
                        $sub->where('estado', EstadoLote::Disponible)
                            ->where(function ($dateQuery) {
                                $dateQuery->whereNull('fecha_vencimiento')
                                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                            });
                    });
            })
            ->get();

        $ordenados = $stocks->sortBy(function ($st) {
            return $st->lote?->fecha_vencimiento?->format('Y-m-d') ?? '9999-12-31';
        })->values();

        $totalDisponible = (float) $ordenados->sum(fn ($st) => (float) $st->cantidad);
        if ($totalDisponible < $cantidadRequerida) {
            throw new \RuntimeException(sprintf(
                'Stock insuficiente en la bodega de origen. Disponible: %f, Requerido: %f',
                $totalDisponible,
                $cantidadRequerida
            ));
        }

        $restante = $cantidadRequerida;
        foreach ($ordenados as $stock) {
            if ($restante <= 0.0) {
                break;
            }

            $aTrasladar = min(floatval($stock->cantidad), $restante);
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
