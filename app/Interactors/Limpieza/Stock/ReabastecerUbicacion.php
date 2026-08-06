<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Stock;

use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class ReabastecerUbicacion
{
    public function __construct(
        private readonly ConsumirStock $consumirStock,
    ) {}

    public function execute(ReabastecerUbicacionData $dto): void
    {
        if (! in_array($dto->tipoDestino, ['habitacion', 'espacio', 'ubicacion'], true)) {
            throw new \InvalidArgumentException("Tipo de destino inválido: {$dto->tipoDestino}");
        }

        $stockableType = match ($dto->tipoDestino) {
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
        };

        $ubicacionDestinoId = null;
        if ($stockableType === Habitacion::class) {
            $habitacion = Habitacion::find($dto->destinoId);
            $ubicacionDestinoId = $habitacion?->ubicacion_id;
        } elseif ($stockableType === Espacio::class) {
            $espacio = Espacio::find($dto->destinoId);
            $ubicacionDestinoId = $espacio?->ubicacion_id;
        } elseif ($stockableType === Ubicacion::class) {
            $ubicacionDestinoId = $dto->destinoId;
        }

        $varianteIds = array_map(fn ($item) => $item->productoVarianteId, $dto->items);
        /** @var array<int, ProductoVariante> $variants */
        $variants = ProductoVariante::whereIn('id', array_unique($varianteIds))
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($stockableType, $dto, $ubicacionDestinoId, $variants) {
            foreach ($dto->items as $item) {
                $variant = $variants[$item->productoVarianteId] ?? null;
                $productoId = $variant ? $variant->producto_id : 0;

                $detalle = $this->consumirStock->execute(
                    productoId: $productoId,
                    cantidadRequerida: $item->cantidad,
                    ubicacionId: $dto->bodegaOrigenId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: $item->productoVarianteId,
                    creadoPorId: $dto->creadoPorId,
                    notas: $dto->notas,
                    referencia: "Reabastecimiento {$stockableType} #{$dto->destinoId}",
                    ubicacionDestinoId: $ubicacionDestinoId,
                );

                $cantidadConsumida = array_sum(array_column($detalle, 'cantidad'));
                $loteConsumidoId = collect($detalle)->firstWhere('lote_id', '!==', null)['lote_id'] ?? null;

                $existing = SharedStock::firstOrNew([
                    'stockable_type' => $stockableType,
                    'stockable_id' => $dto->destinoId,
                    'producto_variante_id' => $item->productoVarianteId,
                ]);
                $existing->cantidad_actual = ($existing->cantidad_actual ?? 0) + $cantidadConsumida;
                if (! $existing->cantidad_ideal) {
                    $existing->cantidad_ideal = $existing->cantidad_actual;
                }
                if ($loteConsumidoId) {
                    $existing->lote_id = abs((int) $loteConsumidoId);
                }
                $existing->save();
            }
        });
    }
}
