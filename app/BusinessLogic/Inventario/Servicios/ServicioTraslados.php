<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Servicios;

use App\BusinessLogic\Inventario\Validacion\ValidacionLotes;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;

class ServicioTraslados
{
    public function __construct(
        private readonly StockRepositorioInterface $stockRepositorio,
        private readonly MovimientoStockRepositorioInterface $movimientoStockRepositorio,
        private readonly LoteRepositorioInterface $loteRepositorio,
        private readonly ValidacionLotes $validacion,
    ) {}

    public function ejecutarTrasladoLote(
        Lote $lote,
        int $destinoId,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null,
        ?int $destinoDetalleId = null,
    ): void {
        $origenId = $lote->ubicacion_id ?? throw new \RuntimeException('El lote no tiene ubicación de origen.');
        $this->validacion->validarTraslado($origenId, $destinoId);

        $stockOrigen = $this->stockRepositorio->buscarPorLoteUbicacion($lote->id, $origenId)
            ?? throw new \RuntimeException('Stock de origen no encontrado.');

        $this->validacion->validarStockSuficiente($stockOrigen, $lote);

        $stockOrigen->cantidad -= $lote->cantidad_disponible;

        if ($stockOrigen->cantidad <= 0.0) {
            $this->stockRepositorio->eliminar($stockOrigen);
        } else {
            $this->stockRepositorio->guardar($stockOrigen);
        }

        $stockDestino = $this->stockRepositorio->buscarPorLoteUbicacion($lote->id, $destinoId);

        if ($stockDestino !== null) {
            $stockDestino->cantidad += $lote->cantidad_disponible;
            if ($destinoDetalleId !== null) {
                $stockDestino->ubicacion_detalle_id = abs($destinoDetalleId);
            }
            $this->stockRepositorio->guardar($stockDestino);
        } else {
            $this->stockRepositorio->crear([
                'producto_id' => $lote->producto_id,
                'producto_variante_id' => $lote->producto_variante_id,
                'lote_id' => $lote->id,
                'ubicacion_id' => $destinoId,
                'ubicacion_detalle_id' => $destinoDetalleId,
                'cantidad' => $lote->cantidad_disponible,
            ]);
        }

        $lote->ubicacion_id = max(0, $destinoId);
        if ($destinoDetalleId !== null) {
            $lote->ubicacion_detalle_id = abs($destinoDetalleId);
        }
        $this->loteRepositorio->guardar($lote);

        $costoTotal = $lote->costo_unitario !== null
            ? $lote->costo_unitario * $lote->cantidad_disponible
            : null;

        $this->movimientoStockRepositorio->registrar([
            'tipo' => 'TRASLADO',
            'lote_id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'cantidad' => $lote->cantidad_disponible,
            'costo_unitario' => $lote->costo_unitario,
            'costo_total' => $costoTotal,
            'ubicacion_origen_id' => $origenId,
            'ubicacion_destino_id' => $destinoId,
            'documento_tipo' => 'traslado_lote',
            'referencia' => $referencia ?: sprintf(
                'Traslado de lote %s de ubicación %d a %d',
                $lote->codigo_lote,
                $origenId,
                $destinoId,
            ),
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);
    }

    public function ejecutarTrasladoEntreBodegas(
        int $productoId,
        int $loteId,
        float $cantidad,
        int $origenId,
        int $destinoId,
        ?int $productoVarianteId = null,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null,
        ?int $ubicacionDestinoDetalleId = null,
        ?Lote $lote = null,
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a trasladar debe ser mayor a cero.');
        }
        $this->validacion->validarTraslado($origenId, $destinoId);

        $stockOrigen = $this->stockRepositorio->buscarPorLoteUbicacion($loteId, $origenId)
            ?? throw new \RuntimeException('Stock de origen no encontrado.');

        if ($stockOrigen->cantidad < $cantidad) {
            throw new \RuntimeException(
                "Stock insuficiente en la bodega origen. Disponible: {$stockOrigen->cantidad}, Requerido: {$cantidad}"
            );
        }

        $stockOrigen->cantidad -= $cantidad;
        if ($stockOrigen->cantidad <= 0.0) {
            $this->stockRepositorio->eliminar($stockOrigen);
        } else {
            $this->stockRepositorio->guardar($stockOrigen);
        }

        $stockDestino = $this->stockRepositorio->buscarPorLoteUbicacion($loteId, $destinoId);

        if ($stockDestino !== null) {
            $stockDestino->cantidad += $cantidad;
            $this->stockRepositorio->guardar($stockDestino);
        } else {
            $this->stockRepositorio->crear([
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => $loteId,
                'ubicacion_id' => $destinoId,
                'ubicacion_detalle_id' => $ubicacionDestinoDetalleId,
                'cantidad' => $cantidad,
            ]);
        }

        $costoUnitario = $lote?->costo_unitario;
        $costoTotal = $costoUnitario !== null ? $costoUnitario * $cantidad : null;

        $this->movimientoStockRepositorio->registrar([
            'tipo' => 'TRASLADO',
            'lote_id' => $loteId,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'ubicacion_origen_id' => $origenId,
            'ubicacion_destino_id' => $destinoId,
            'documento_tipo' => 'traslado',
            'referencia' => $referencia ?: sprintf('Traslado de bodega %d a bodega %d', $origenId, $destinoId),
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);
    }
}
