<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

final class RegistrarEntradaInsumosLavanderia
{
    /**
     * @param  list<array{producto_variante_id: int, cantidad: float, lote_id?: int|null, codigo_lote?: string|null, costo_unitario?: float|null, fecha_vencimiento?: string|null, notas?: string|null}>  $items
     * @param  int|array<int>  $ubicacionLavanderiaId
     * @return array{total_items: int, total_cantidad: float}
     */
    public function execute(
        string $tipoOrigen,
        array $items,
        int|array $ubicacionLavanderiaId,
        ?int $bodegaOrigenId = null,
        ?int $creadoPorId = null,
        ?string $documentoReferencia = null,
        ?string $notasGenerales = null,
    ): array {
        $lavanderiaId = is_array($ubicacionLavanderiaId)
            ? ($ubicacionLavanderiaId[0] ?? Ubicacion::query()->where('tipo', 'lavanderia')->value('id'))
            : $ubicacionLavanderiaId;

        if ($lavanderiaId === null) {
            throw new \InvalidArgumentException('Ubicación de lavandería no válida.');
        }

        $itemsValidos = array_filter($items, fn (array $item): bool => $item['producto_variante_id'] > 0 && $item['cantidad'] > 0.0);

        if (empty($itemsValidos)) {
            throw new \InvalidArgumentException('Debe ingresar al menos un insumo con cantidad mayor a cero.');
        }

        if ($tipoOrigen === 'bodega' && ($bodegaOrigenId === null || $bodegaOrigenId <= 0)) {
            throw new \InvalidArgumentException('Debe seleccionar el almacén / bodega de origen.');
        }

        return DB::transaction(function () use (
            $tipoOrigen,
            $itemsValidos,
            $lavanderiaId,
            $bodegaOrigenId,
            $creadoPorId,
            $documentoReferencia,
            $notasGenerales
        ): array {
            $totalItems = 0;
            $totalCantidad = 0.0;

            foreach ($itemsValidos as $item) {
                $varianteId = (int) $item['producto_variante_id'];
                $cantidad = (float) $item['cantidad'];
                $variante = ProductoVariante::query()->with('producto')->findOrFail($varianteId);
                $productoId = (int) $variante->producto_id;
                $notasItem = isset($item['notas']) && trim((string) $item['notas']) !== ''
                    ? trim((string) $item['notas'])
                    : $notasGenerales;

                if ($tipoOrigen === 'bodega') {
                    // Traslado desde Bodega / Almacén Central hacia Lavandería
                    $loteId = isset($item['lote_id']) && (int) $item['lote_id'] > 0 ? (int) $item['lote_id'] : null;

                    $stockOrigenQuery = Stock::query()
                        ->where('ubicacion_id', $bodegaOrigenId)
                        ->where('producto_variante_id', $varianteId);

                    if ($loteId !== null) {
                        $stockOrigenQuery->where('lote_id', $loteId);
                    }

                    $stockOrigen = $stockOrigenQuery->lockForUpdate()->first();

                    if (! $stockOrigen instanceof Stock || (float) $stockOrigen->cantidad < $cantidad) {
                        $disp = $stockOrigen instanceof Stock ? (float) $stockOrigen->cantidad : 0.0;
                        $producto = $variante->producto;
                        $productoNombre = $producto !== null ? (string) $producto->nombre : 'Insumo';
                        throw new \RuntimeException(sprintf(
                            'Stock insuficiente de "%s" en la bodega de origen. Disponible: %.2f, Requerido: %.2f',
                            $productoNombre,
                            $disp,
                            $cantidad
                        ));
                    }

                    $stockOrigen->cantidad = (float) $stockOrigen->cantidad - $cantidad;
                    $stockOrigen->save();

                    $loteFinalId = $stockOrigen->lote_id ? (int) $stockOrigen->lote_id : null;
                    $loteObj = $loteFinalId !== null ? Lote::query()->find($loteFinalId) : null;
                    $costoUnitario = $loteObj?->costo_unitario !== null ? (float) $loteObj->costo_unitario : null;

                    // Incrementar o crear stock en Lavandería
                    $stockLavanderia = Stock::query()
                        ->where('ubicacion_id', $lavanderiaId)
                        ->where('producto_variante_id', $varianteId)
                        ->when(
                            $loteFinalId !== null,
                            fn ($q) => $q->where('lote_id', $loteFinalId),
                            fn ($q) => $q->whereNull('lote_id')
                        )
                        ->lockForUpdate()
                        ->first();

                    if ($stockLavanderia instanceof Stock) {
                        $stockLavanderia->cantidad = (float) $stockLavanderia->cantidad + $cantidad;
                        $stockLavanderia->save();
                    } else {
                        Stock::query()->create([
                            'ubicacion_id' => $lavanderiaId,
                            'producto_id' => $productoId,
                            'producto_variante_id' => $varianteId,
                            'lote_id' => $loteFinalId,
                            'cantidad' => $cantidad,
                        ]);
                    }

                    // Movimiento de Traslado
                    MovimientoStock::query()->create([
                        'tipo' => 'TRASLADO',
                        'lote_id' => $loteFinalId,
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costoUnitario,
                        'costo_total' => $costoUnitario !== null ? $costoUnitario * $cantidad : null,
                        'ubicacion_origen_id' => $bodegaOrigenId,
                        'ubicacion_destino_id' => $lavanderiaId,
                        'documento_tipo' => 'traslado_lavanderia',
                        'referencia' => 'Abastecimiento de Insumos a Lavandería'.($documentoReferencia ? " (Ref: {$documentoReferencia})" : ''),
                        'creado_por_id' => $creadoPorId,
                        'notas' => $notasItem,
                    ]);
                } else {
                    // Ingreso Directo / Compra de Proveedor
                    $codigoLote = isset($item['codigo_lote']) && trim((string) $item['codigo_lote']) !== ''
                        ? trim((string) $item['codigo_lote'])
                        : 'LOTE-LAV-'.strtoupper(uniqid());
                    $costoUnitario = isset($item['costo_unitario']) ? (float) $item['costo_unitario'] : null;
                    $fechaVencimiento = isset($item['fecha_vencimiento']) && trim((string) $item['fecha_vencimiento']) !== '' ? trim((string) $item['fecha_vencimiento']) : null;

                    $lote = Lote::query()->firstOrCreate(
                        [
                            'codigo_lote' => $codigoLote,
                            'producto_id' => $productoId,
                            'producto_variante_id' => $varianteId,
                        ],
                        [
                            'ubicacion_id' => $lavanderiaId,
                            'estado' => EstadoLote::Disponible,
                            'cantidad_inicial' => $cantidad,
                            'cantidad_disponible' => $cantidad,
                            'costo_unitario' => $costoUnitario,
                            'fecha_vencimiento' => $fechaVencimiento,
                            'fecha_recepcion' => now()->toDateString(),
                        ]
                    );

                    $stockLavanderia = Stock::query()
                        ->where('ubicacion_id', $lavanderiaId)
                        ->where('producto_variante_id', $varianteId)
                        ->where('lote_id', $lote->id)
                        ->lockForUpdate()
                        ->first();

                    if ($stockLavanderia instanceof Stock) {
                        $stockLavanderia->cantidad = (float) $stockLavanderia->cantidad + $cantidad;
                        $stockLavanderia->save();
                    } else {
                        Stock::query()->create([
                            'ubicacion_id' => $lavanderiaId,
                            'producto_id' => $productoId,
                            'producto_variante_id' => $varianteId,
                            'lote_id' => $lote->id,
                            'cantidad' => $cantidad,
                        ]);
                    }

                    MovimientoStock::query()->create([
                        'tipo' => 'ENTRADA_STOCK',
                        'lote_id' => $lote->id,
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costoUnitario,
                        'costo_total' => $costoUnitario !== null ? $costoUnitario * $cantidad : null,
                        'ubicacion_origen_id' => null,
                        'ubicacion_destino_id' => $lavanderiaId,
                        'documento_tipo' => 'entrada_directa_lavanderia',
                        'referencia' => 'Entrada Directa de Insumos a Lavandería'.($documentoReferencia ? " (Doc: {$documentoReferencia})" : ''),
                        'creado_por_id' => $creadoPorId,
                        'notas' => $notasItem,
                    ]);
                }

                $totalItems++;
                $totalCantidad += $cantidad;
            }

            return [
                'total_items' => $totalItems,
                'total_cantidad' => $totalCantidad,
            ];
        });
    }
}
