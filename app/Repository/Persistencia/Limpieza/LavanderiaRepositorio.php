<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Limpieza;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LavanderiaProceso;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\Shared\Stock as SharedStock;

final readonly class LavanderiaRepositorio implements LavanderiaRepositorioInterface
{
    /** @param int|array<int> $ubicacionId */
    public function buscarStockPorIdConLock(int $stockId, int|array $ubicacionId): Stock
    {
        /** @var Stock $stock */
        $stock = Stock::query()
            ->with(['variante.producto', 'lote'])
            ->whereKey($stockId)
            ->when(
                is_array($ubicacionId),
                fn ($query) => $query->whereIn('ubicacion_id', $ubicacionId),
                fn ($query) => $query->where('ubicacion_id', $ubicacionId),
            )
            ->lockForUpdate()
            ->firstOrFail();

        return $stock;
    }

    public function reponerDestinoStock(string $stockableType, int $destinoId, ?int $varianteId, float $cantidad, ?int $loteId): void
    {
        $existing = SharedStock::query()
            ->where('stockable_type', $stockableType)
            ->where('stockable_id', $destinoId)
            ->where('producto_variante_id', $varianteId)
            ->lockForUpdate()
            ->first();

        if (! $existing instanceof SharedStock) {
            $existing = new SharedStock([
                'stockable_type' => $stockableType,
                'stockable_id' => $destinoId,
                'producto_variante_id' => $varianteId,
            ]);
        }

        $existing->cantidad_actual = (float) ($existing->cantidad_actual ?? 0) + $cantidad;
        if (! $existing->cantidad_ideal) {
            $existing->cantidad_ideal = $existing->cantidad_actual;
        }
        if ($loteId !== null && $loteId >= 0) {
            $existing->lote_id = $loteId;
        }
        $existing->save();
    }

    public function resolverUbicacionDestino(string $stockableType, int $destinoId): ?int
    {
        if ($stockableType === Habitacion::class) {
            return Habitacion::query()->findOrFail($destinoId)->ubicacion_id;
        }

        if ($stockableType === Espacio::class) {
            return Espacio::query()->findOrFail($destinoId)->ubicacion_id;
        }

        Ubicacion::query()->findOrFail($destinoId);

        return $destinoId;
    }

    public function entradaInsumosDesdeBodega(
        int $bodegaOrigenId,
        int $lavanderiaId,
        int $varianteId,
        int $productoId,
        float $cantidad,
        ?int $loteId,
        ?int $creadoPorId,
        ?string $referencia,
        ?string $notas
    ): void {
        $stockOrigenQuery = Stock::query()
            ->where('ubicacion_id', $bodegaOrigenId)
            ->where('producto_variante_id', $varianteId);

        if ($loteId !== null) {
            $stockOrigenQuery->where('lote_id', $loteId);
        }

        /** @var Stock|null $stockOrigen */
        $stockOrigen = $stockOrigenQuery->lockForUpdate()->first();

        if (! $stockOrigen instanceof Stock || (float) $stockOrigen->cantidad < $cantidad) {
            $disp = $stockOrigen instanceof Stock ? (float) $stockOrigen->cantidad : 0.0;
            throw new \RuntimeException(sprintf(
                'Stock insuficiente en la bodega de origen. Disponible: %.2f, Requerido: %.2f',
                $disp,
                $cantidad
            ));
        }

        $stockOrigen->cantidad = (float) $stockOrigen->cantidad - $cantidad;
        $stockOrigen->save();

        $loteFinalId = $stockOrigen->lote_id ? (int) $stockOrigen->lote_id : null;
        $loteObj = $loteFinalId !== null ? Lote::query()->find($loteFinalId) : null;
        $costoUnitario = $loteObj?->costo_unitario !== null ? (float) $loteObj->costo_unitario : null;

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
            'referencia' => 'Abastecimiento de Insumos a Lavandería'.($referencia ? " (Ref: {$referencia})" : ''),
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);
    }

    public function entradaDirectaInsumos(
        int $lavanderiaId,
        int $productoId,
        int $varianteId,
        float $cantidad,
        ?string $codigoLote,
        ?float $costoUnitario,
        ?string $fechaVencimiento,
        ?int $creadoPorId,
        ?string $referencia,
        ?string $notas
    ): void {
        $codigoLoteFinal = $codigoLote && trim($codigoLote) !== ''
            ? trim($codigoLote)
            : 'LOTE-LAV-'.strtoupper(uniqid());

        $lote = Lote::query()->firstOrCreate(
            [
                'codigo_lote' => $codigoLoteFinal,
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
            'referencia' => 'Entrada Directa de Insumos a Lavandería'.($referencia ? " (Doc: {$referencia})" : ''),
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);
    }

    public function entradaDirectaPrenda(
        int $lavanderiaId,
        int $productoId,
        int $varianteId,
        float $cantidad,
        ?int $loteId,
        ?string $tipoOrigen,
        ?int $origenId,
        ?int $creadoPorId,
        ?string $notas,
        string $referencia
    ): void {
        $loteIdFinal = $loteId;
        if ($loteIdFinal === null || $loteIdFinal <= 0) {
            $loteExistente = Lote::query()
                ->where('producto_variante_id', $varianteId)
                ->where('cantidad_disponible', '>', 0)
                ->latest('id')
                ->first();

            if ($loteExistente instanceof Lote) {
                $loteIdFinal = (int) $loteExistente->id;
            } else {
                $codigoLoteDefault = 'LOTE-'.strval($varianteId);
                $loteNuevo = Lote::query()->firstOrCreate(
                    [
                        'codigo_lote' => $codigoLoteDefault,
                        'producto_id' => $productoId,
                        'producto_variante_id' => $varianteId,
                    ],
                    [
                        'ubicacion_id' => $lavanderiaId,
                        'estado' => EstadoLote::Disponible,
                        'cantidad_disponible' => 0.0,
                        'cantidad_inicial' => 0.0,
                        'fecha_recepcion' => now()->toDateString(),
                    ]
                );
                $loteIdFinal = (int) $loteNuevo->id;
            }
        }

        if ($tipoOrigen !== null && $origenId !== null && $origenId > 0) {
            if ($tipoOrigen === 'habitacion' || $tipoOrigen === 'espacio') {
                $stockableType = $tipoOrigen === 'habitacion' ? Habitacion::class : Espacio::class;
                $stockOrigen = SharedStock::query()
                    ->where('stockable_type', $stockableType)
                    ->where('stockable_id', $origenId)
                    ->where('producto_variante_id', $varianteId)
                    ->when($loteIdFinal > 0, fn ($q) => $q->where('lote_id', $loteIdFinal))
                    ->lockForUpdate()
                    ->first();

                if ($stockOrigen instanceof SharedStock) {
                    $stockOrigen->cantidad_actual = max(0.0, (float) $stockOrigen->cantidad_actual - $cantidad);
                    $stockOrigen->save();
                }
            } elseif ($tipoOrigen === 'ubicacion' || $tipoOrigen === 'carrito') {
                $stockOrigen = Stock::query()
                    ->where('ubicacion_id', $origenId)
                    ->where('producto_variante_id', $varianteId)
                    ->where('lote_id', $loteIdFinal)
                    ->lockForUpdate()
                    ->first();

                if ($stockOrigen instanceof Stock) {
                    $stockOrigen->cantidad = max(0.0, (float) $stockOrigen->cantidad - $cantidad);
                    $stockOrigen->save();
                }
            }
        }

        $stock = Stock::query()
            ->where('producto_id', $productoId)
            ->where('producto_variante_id', $varianteId)
            ->where('lote_id', $loteIdFinal)
            ->where('ubicacion_id', $lavanderiaId)
            ->lockForUpdate()
            ->first();

        if ($stock instanceof Stock) {
            $stock->cantidad = (float) $stock->cantidad + $cantidad;
            $stock->save();
        } else {
            Stock::query()->create([
                'producto_id' => $productoId,
                'producto_variante_id' => $varianteId,
                'lote_id' => $loteIdFinal,
                'ubicacion_id' => $lavanderiaId,
                'cantidad' => $cantidad,
            ]);
        }

        LavanderiaProceso::query()->create([
            'producto_id' => $productoId,
            'producto_variante_id' => $varianteId,
            'lote_id' => $loteIdFinal,
            'cantidad' => $cantidad,
            'estado' => 'en_proceso',
        ]);

        $ubicacionOrigenId = (in_array($tipoOrigen, ['ubicacion', 'carrito'], true) && $origenId !== null && $origenId > 0) ? $origenId : null;

        MovimientoStock::query()->create([
            'tipo' => 'ENTRADA_LAVANDERIA',
            'lote_id' => $loteIdFinal,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'ubicacion_origen_id' => $ubicacionOrigenId,
            'ubicacion_destino_id' => $lavanderiaId,
            'documento_tipo' => 'lavanderia',
            'referencia' => $referencia,
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);
    }

    /** @param int|array<int> $ubicacionLavanderiaId */
    public function descontarStockJornada(
        int $stockId,
        int|array $ubicacionLavanderiaId,
        float $cantidad,
        string $referencia,
        ?int $creadoPorId,
        ?string $notas,
        string $tipoMovimiento,
        string $documentoTipo = 'jornada_lavanderia'
    ): Stock {
        /** @var Stock $stock */
        $stock = Stock::query()
            ->with(['lote', 'variante.producto'])
            ->whereKey($stockId)
            ->when(
                is_array($ubicacionLavanderiaId),
                fn ($query) => $query->whereIn('ubicacion_id', $ubicacionLavanderiaId),
                fn ($query) => $query->where('ubicacion_id', $ubicacionLavanderiaId),
            )
            ->lockForUpdate()
            ->firstOrFail();

        if ((float) $stock->cantidad < $cantidad) {
            throw new \RuntimeException(sprintf(
                'Stock insuficiente en lavandería. Disponible: %.2f, Requerido: %.2f',
                (float) $stock->cantidad,
                $cantidad
            ));
        }

        $stock->cantidad = (float) $stock->cantidad - $cantidad;
        $stock->save();

        $costoUnitario = $stock->lote?->costo_unitario !== null ? (float) $stock->lote->costo_unitario : null;
        $costoTotal = $costoUnitario !== null ? $costoUnitario * $cantidad : null;
        $ubicacionId = (int) $stock->ubicacion_id;

        MovimientoStock::query()->create([
            'tipo' => $tipoMovimiento,
            'lote_id' => $stock->lote_id,
            'producto_id' => $stock->producto_id,
            'cantidad' => -$cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'ubicacion_origen_id' => $ubicacionId,
            'ubicacion_destino_id' => null,
            'documento_tipo' => $documentoTipo,
            'referencia' => $referencia,
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);

        return $stock;
    }

    public function enviarALavanderia(
        int $stockId,
        int $lavanderiaId,
        float $cantidad,
        string $tipo,
        ?int $creadoPorId,
        ?string $notas
    ): void {
        /** @var SharedStock $stock */
        $stock = SharedStock::with(['lote', 'variante.producto'])
            ->whereKey($stockId)
            ->lockForUpdate()
            ->firstOrFail();

        $origenNombre = ucfirst($tipo)." #{$stock->stockable_id}";
        $productoId = $stock->variante?->producto_id;

        if ($productoId === null) {
            throw new \RuntimeException('El blanco seleccionado no tiene producto asociado.');
        }

        $cantidadEnviar = min((float) $stock->cantidad_actual, $cantidad);
        if ($cantidadEnviar <= 0) {
            return;
        }

        $stock->cantidad_actual = (float) $stock->cantidad_actual - $cantidadEnviar;
        $stock->save();

        $stockLavanderia = Stock::where([
            'producto_id' => $productoId,
            'producto_variante_id' => $stock->producto_variante_id,
            'lote_id' => $stock->lote_id,
            'ubicacion_id' => $lavanderiaId,
        ])->lockForUpdate()->first();

        if ($stockLavanderia) {
            $stockLavanderia->cantidad = (float) $stockLavanderia->cantidad + $cantidadEnviar;
            $stockLavanderia->save();
        } else {
            Stock::create([
                'producto_id' => $productoId,
                'producto_variante_id' => $stock->producto_variante_id,
                'lote_id' => $stock->lote_id,
                'ubicacion_id' => $lavanderiaId,
                'cantidad' => $cantidadEnviar,
            ]);
        }

        $costoUnitarioMov = $stock->lote?->costo_unitario;
        $costoTotalMov = $costoUnitarioMov !== null
            ? $costoUnitarioMov * $cantidadEnviar
            : null;

        MovimientoStock::create([
            'tipo' => 'TRASLADO_LAVANDERIA',
            'lote_id' => $stock->lote_id,
            'producto_id' => $productoId,
            'cantidad' => -$cantidadEnviar,
            'costo_unitario' => $costoUnitarioMov,
            'costo_total' => $costoTotalMov,
            'ubicacion_origen_id' => null,
            'ubicacion_destino_id' => $lavanderiaId,
            'documento_tipo' => 'lavanderia',
            'referencia' => "Envío a lavandería desde {$origenNombre}",
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);

        LavanderiaProceso::create([
            'producto_id' => $productoId,
            'producto_variante_id' => $stock->producto_variante_id,
            'lote_id' => $stock->lote_id,
            'cantidad' => $cantidadEnviar,
            'estado' => 'en_proceso',
        ]);
    }

    public function buscarNombreTurno(int $turnoId): ?string
    {
        /** @var Turno|null $turno */
        $turno = Turno::query()->find($turnoId);

        return $turno !== null ? (string) $turno->nombre : null;
    }
}
