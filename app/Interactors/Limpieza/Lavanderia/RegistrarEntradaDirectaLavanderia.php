<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LavanderiaProceso;
use App\Repository\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

final class RegistrarEntradaDirectaLavanderia
{
    public function execute(
        int $productoVarianteId,
        float $cantidad,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notas = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
        ?int $loteId = null,
    ): void {
        if ($cantidad <= 0.0) {
            throw new \InvalidArgumentException('La cantidad a ingresar debe ser mayor a cero.');
        }

        DB::transaction(function () use ($productoVarianteId, $cantidad, $ubicacionLavanderiaId, $creadoPorId, $notas, $tipoOrigen, $origenId, $loteId): void {
            $this->procesarItem(
                productoVarianteId: $productoVarianteId,
                cantidad: $cantidad,
                ubicacionLavanderiaId: $ubicacionLavanderiaId,
                creadoPorId: $creadoPorId,
                notas: $notas,
                tipoOrigen: $tipoOrigen,
                origenId: $origenId,
                loteId: $loteId,
            );
        });
    }

    /**
     * @param  list<array{producto_variante_id: int, lote_id?: int|null, cantidad: float, notas?: string|null}>  $items
     * @return array{total_items: int, total_piezas: float}
     */
    public function ejecutarLote(
        array $items,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notasGenerales = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
    ): array {
        $itemsValidos = array_filter($items, fn (array $item): bool => $item['cantidad'] > 0.0);

        if (empty($itemsValidos)) {
            throw new \InvalidArgumentException('Debe ingresar al menos un producto con cantidad mayor a cero.');
        }

        return DB::transaction(function () use ($itemsValidos, $ubicacionLavanderiaId, $creadoPorId, $notasGenerales, $tipoOrigen, $origenId): array {
            $totalPiezas = 0.0;
            $totalItems = 0;

            foreach ($itemsValidos as $item) {
                $varianteId = $item['producto_variante_id'];
                $cantidad = $item['cantidad'];
                $itemLoteId = isset($item['lote_id']) && $item['lote_id'] > 0
                    ? $item['lote_id']
                    : null;
                $notasRaw = $item['notas'] ?? null;
                $itemNotas = $notasRaw !== null && trim($notasRaw) !== ''
                    ? trim($notasRaw)
                    : $notasGenerales;

                $this->procesarItem(
                    productoVarianteId: $varianteId,
                    cantidad: $cantidad,
                    ubicacionLavanderiaId: $ubicacionLavanderiaId,
                    creadoPorId: $creadoPorId,
                    notas: $itemNotas,
                    tipoOrigen: $tipoOrigen,
                    origenId: $origenId,
                    loteId: $itemLoteId,
                );

                $totalPiezas += $cantidad;
                $totalItems++;
            }

            return [
                'total_items' => $totalItems,
                'total_piezas' => $totalPiezas,
            ];
        });
    }

    private function procesarItem(
        int $productoVarianteId,
        float $cantidad,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notas = null,
        ?string $tipoOrigen = null,
        ?int $origenId = null,
        ?int $loteId = null,
    ): void {
        $variante = ProductoVariante::query()
            ->with('producto')
            ->whereKey($productoVarianteId)
            ->firstOrFail();

        $loteIdFinal = $loteId;
        if ($loteIdFinal === null || $loteIdFinal <= 0) {
            $loteExistente = Lote::query()
                ->where('producto_variante_id', $productoVarianteId)
                ->where('cantidad_disponible', '>', 0)
                ->latest('id')
                ->first();

            if ($loteExistente instanceof Lote) {
                $loteIdFinal = (int) $loteExistente->id;
            } else {
                $codigoLoteDefault = 'LOTE-'.($variante->codigo ?: (string) $variante->id);
                $loteNuevo = Lote::query()->firstOrCreate(
                    [
                        'codigo_lote' => $codigoLoteDefault,
                        'producto_id' => $variante->producto_id,
                        'producto_variante_id' => $productoVarianteId,
                    ],
                    [
                        'estado' => EstadoLote::Disponible,
                        'cantidad_disponible' => 0.0,
                        'cantidad_inicial' => 0.0,
                        'fecha_recepcion' => now()->toDateString(),
                    ]
                );
                $loteIdFinal = (int) $loteNuevo->id;
            }
        }

        // 1. Descontar stock del origen si aplica
        if ($tipoOrigen !== null && $origenId !== null && $origenId > 0) {
            if ($tipoOrigen === 'habitacion' || $tipoOrigen === 'espacio') {
                $stockableType = $tipoOrigen === 'habitacion' ? Habitacion::class : Espacio::class;
                $stockOrigen = SharedStock::query()
                    ->where('stockable_type', $stockableType)
                    ->where('stockable_id', $origenId)
                    ->where('producto_variante_id', $productoVarianteId)
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
                    ->where('producto_variante_id', $productoVarianteId)
                    ->where('lote_id', $loteIdFinal)
                    ->lockForUpdate()
                    ->first();

                if ($stockOrigen instanceof Stock) {
                    $stockOrigen->cantidad = max(0.0, (float) $stockOrigen->cantidad - $cantidad);
                    $stockOrigen->save();
                }
            }
        }

        // 2. Aumentar stock en la ubicación de lavandería
        $stock = Stock::query()
            ->where('producto_id', $variante->producto_id)
            ->where('producto_variante_id', $productoVarianteId)
            ->where('lote_id', $loteIdFinal)
            ->where('ubicacion_id', $ubicacionLavanderiaId)
            ->lockForUpdate()
            ->first();

        if ($stock instanceof Stock) {
            $stock->cantidad = (float) $stock->cantidad + $cantidad;
            $stock->save();
        } else {
            Stock::query()->create([
                'producto_id' => $variante->producto_id,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => $loteIdFinal,
                'ubicacion_id' => $ubicacionLavanderiaId,
                'cantidad' => $cantidad,
            ]);
        }

        // 3. Registrar prenda en proceso de lavandería
        LavanderiaProceso::query()->create([
            'producto_id' => $variante->producto_id,
            'producto_variante_id' => $productoVarianteId,
            'lote_id' => $loteIdFinal,
            'cantidad' => $cantidad,
            'estado' => 'en_proceso',
        ]);

        // 4. Crear movimiento de stock con trazabilidad
        $referencia = $this->construirReferencia($tipoOrigen, $origenId);
        $ubicacionOrigenId = (in_array($tipoOrigen, ['ubicacion', 'carrito'], true) && $origenId !== null && $origenId > 0) ? $origenId : null;

        MovimientoStock::query()->create([
            'tipo' => 'ENTRADA_LAVANDERIA',
            'lote_id' => $loteIdFinal,
            'producto_id' => $variante->producto_id,
            'cantidad' => $cantidad,
            'ubicacion_origen_id' => $ubicacionOrigenId,
            'ubicacion_destino_id' => $ubicacionLavanderiaId,
            'documento_tipo' => 'lavanderia',
            'referencia' => $referencia,
            'creado_por_id' => $creadoPorId,
            'notas' => $notas,
        ]);
    }

    private function construirReferencia(?string $tipoOrigen, ?int $origenId): string
    {
        if ($tipoOrigen === null || $origenId === null || $origenId <= 0) {
            return 'Entrada directa a lavandería';
        }

        return match ($tipoOrigen) {
            'habitacion' => "Entrada a lavandería desde Habitación #{$origenId}",
            'espacio' => "Entrada a lavandería desde Espacio #{$origenId}",
            'ubicacion' => "Entrada a lavandería desde Almacén/Bodega #{$origenId}",
            'carrito' => "Entrada a lavandería desde Carrito #{$origenId}",
            default => "Entrada a lavandería desde {$tipoOrigen} #{$origenId}",
        };
    }
}
