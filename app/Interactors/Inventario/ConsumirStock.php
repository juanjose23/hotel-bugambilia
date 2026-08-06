<?php

declare(strict_types=1);

namespace App\Interactors\Inventario;

use App\BusinessLogic\Inventario\Servicios\ServicioConsumos;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Queries\Inventario\Stock\ObtenerStockParaConsumo;
use Illuminate\Support\Facades\DB;

class ConsumirStock
{
    public function __construct(
        private readonly ServicioConsumos $servicioConsumos,
        private readonly ObtenerStockParaConsumo $obtenerStockParaConsumo,
    ) {}

    /**
     * Consume stock of a product from a specific warehouse using FEFO strategy.
     *
     * @return array<int, array{stock_id: int, lote_id: int|null, cantidad: float}>
     */
    public function execute(
        int $productoId,
        float $cantidadRequerida,
        int $ubicacionId,
        string $tipoMovimiento = 'CONSUMO',
        ?int $productoVarianteId = null,
        ?int $documentoId = null,
        ?string $documentoTipo = null,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null,
        ?int $ubicacionDestinoId = null,
    ): array {
        if ($cantidadRequerida <= 0) {
            throw new \InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $productoId,
            $cantidadRequerida,
            $ubicacionId,
            $tipoMovimiento,
            $productoVarianteId,
            $documentoId,
            $documentoTipo,
            $creadoPorId,
            $referencia,
            $notas,
            $ubicacionDestinoId,
        ) {
            $stocks = $this->obtenerStockParaConsumo->ejecutar(
                productoId: $productoId,
                ubicacionId: $ubicacionId,
                productoVarianteId: $productoVarianteId
            );

            // 2. Ordenar usando FEFO (fecha de vencimiento más próxima primero, nulls al final)
            $ordenados = $stocks->sortBy(function ($stock) {
                return $stock->lote?->fecha_vencimiento?->format('Y-m-d') ?? '9999-12-31';
            })->values();

            $sum = $ordenados->sum('cantidad');
            $totalDisponible = is_numeric($sum) ? (float) $sum : 0.0;
            if ($totalDisponible < $cantidadRequerida) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente en la bodega. Disponible: %f, Requerido: %f',
                    $totalDisponible,
                    $cantidadRequerida
                ));
            }

            $detalleConsumo = [];
            $restante = $cantidadRequerida;

            foreach ($ordenados as $stock) {
                if ($restante <= 0.0) {
                    break;
                }

                $aConsumir = min((float) $stock->cantidad, $restante);

                $detalleConsumo[] = $this->servicioConsumos->ejecutarConsumoDeStock(
                    stock: $stock,
                    cantidad: $aConsumir,
                    tipoMovimiento: $tipoMovimiento,
                    ubicacionDestinoId: $ubicacionDestinoId,
                    documentoId: $documentoId,
                    documentoTipo: $documentoTipo,
                    creadoPorId: $creadoPorId,
                    referencia: $referencia ?: "Consumo FEFO bodega {$ubicacionId}",
                    notas: $notas,
                );

                $restante -= $aConsumir;
            }

            return $detalleConsumo;
        });
    }
}
