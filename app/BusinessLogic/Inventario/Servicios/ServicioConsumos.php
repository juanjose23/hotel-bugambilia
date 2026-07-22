<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;

class ServicioConsumos
{
    public function __construct(
        private readonly MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private readonly StockRepositorioInterface $stockRepositorio,
        private readonly LoteRepositorioInterface $loteRepositorio,
    ) {}

    /**
     * @return array{stock_id: int, lote_id: int|null, cantidad: float}
     */
    public function ejecutarConsumoDeStock(
        Stock $stock,
        float $cantidad,
        string $tipoMovimiento,
        ?int $ubicacionDestinoId = null,
        ?int $documentoId = null,
        ?string $documentoTipo = null,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null,
    ): array {
        $stock->cantidad -= $cantidad;
        if ($stock->cantidad <= 0.0) {
            $this->stockRepositorio->eliminar($stock);
        } else {
            $this->stockRepositorio->guardar($stock);
        }

        $isTraslado = in_array($tipoMovimiento, ['TRASLADO', 'MOV_TRANSFERENCIA'], true);
        $costoUnitario = null;
        if ($stock->lote_id !== null && ! $isTraslado) {
            $lote = $stock->lote;
            if ($lote) {
                $lote->cantidad_disponible -= $cantidad;
                if ($lote->cantidad_disponible <= 0.0) {
                    $lote->cantidad_disponible = 0.0;
                    $lote->estado = EstadoLote::Agotado;
                }
                $this->loteRepositorio->guardar($lote);
                $costoUnitario = $lote->costo_unitario;
            }
        }

        $costoTotal = $costoUnitario !== null
            ? $costoUnitario * $cantidad
            : null;

        $this->movimientoStockRepositorio->registrar([
            'tipo' => $tipoMovimiento,
            'lote_id' => $stock->lote_id,
            'producto_id' => $stock->producto_id,
            'cantidad' => -$cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'ubicacion_origen_id' => $stock->ubicacion_id,
            'ubicacion_destino_id' => $ubicacionDestinoId,
            'documento_tipo' => $documentoTipo ?: 'consumo',
            'documento_id' => $documentoId,
            'referencia' => $referencia ?: "Consumo FEFO bodega {$stock->ubicacion_id}",
            'creado_por_id' => $creadoPorId,
            'notas' => $notes = $notas,
        ]);

        return [
            'stock_id' => $stock->id,
            'lote_id' => $stock->lote_id,
            'cantidad' => $cantidad,
        ];
    }
}
