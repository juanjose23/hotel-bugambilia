<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

use App\Models\Habitaciones\Habitacion;
use App\Models\Habitaciones\HabitacionStock;
use App\Models\Inventario\ProductoKit;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use App\UseCases\Inventario\Queries\Stock\VerificarStockPack;
use Illuminate\Support\Facades\DB;

class AsignarPackAHabitacion
{
    /** @return array<int, array<string, mixed>> */
    public function execute(
        int $habitacionId,
        int $productoPackId,
        int $bodegaOrigenId,
        float $cantidadPacks = 1.0,
        ?int $creadoPorId = null,
        ?string $referencia = null,
    ): array {
        if ($cantidadPacks <= 0) {
            throw new \InvalidArgumentException('La cantidad de packs debe ser mayor a cero.');
        }

        $habitacion = Habitacion::findOrFail($habitacionId);

        $verificacion = app(VerificarStockPack::class)->ejecutar(
            productoPackId: $productoPackId,
            bodegaOrigenId: $bodegaOrigenId,
            cantidadPacks: $cantidadPacks,
        );

        if (! $verificacion['suficiente']) {
            $errores = [];
            foreach ($verificacion['items'] as $item) {
                if (! $item['suficiente']) {
                    $errores[] = sprintf(
                        '%s — %s: disponible %s, necesario %s',
                        $item['producto'],
                        $item['variante'],
                        $item['disponible'],
                        $item['necesario'],
                    );
                }
            }

            throw new \RuntimeException(
                'Stock insuficiente para completar el pack:'.PHP_EOL.implode(PHP_EOL, $errores)
            );
        }

        $items = ProductoKit::with('variante.producto')->where('producto_padre_id', $productoPackId)->get();

        return DB::transaction(function () use ($habitacion, $items, $bodegaOrigenId, $cantidadPacks, $creadoPorId, $referencia, $productoPackId) {
            $resultado = [];

            foreach ($items as $item) {
                $cantidadTotal = $item->cantidad * $cantidadPacks;
                $variante = $item->variante;

                if (! $variante) {
                    throw new \RuntimeException("Item del kit ID {$item->id} no tiene variante asociada.");
                }

                $producto = $variante->producto;
                if (! $producto) {
                    throw new \RuntimeException("Variante ID {$variante->id} no tiene producto asociado.");
                }

                $consumo = app(ConsumirStock::class)->execute(
                    productoId: $producto->id,
                    cantidadRequerida: $cantidadTotal,
                    ubicacionId: $bodegaOrigenId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: $variante->id,
                    documentoId: $habitacion->id,
                    documentoTipo: 'habitacion',
                    creadoPorId: $creadoPorId,
                    referencia: $referencia ?: sprintf('Pack a habitación %s (%s)', $habitacion->codigo, $habitacion->nombre ?? ''),
                    notas: "Asignación de pack producto ID {$productoPackId} x{$cantidadPacks}",
                    ubicacionDestinoId: $habitacion->ubicacion_id,
                );

                $loteConsumidoId = collect($consumo)->firstWhere('lote_id', '!==', null)['lote_id'] ?? $item->lote_id;

                $stockHabitacion = HabitacionStock::withTrashed()
                    ->where('habitacion_id', $habitacion->id)
                    ->where('producto_variante_id', $variante->id)
                    ->first();

                if ($stockHabitacion) {
                    if ($stockHabitacion->trashed()) {
                        $stockHabitacion->restore();
                    }
                    $stockHabitacion->cantidad_actual += $cantidadTotal;
                    $stockHabitacion->cantidad_ideal += $cantidadTotal;
                    if ($loteConsumidoId) {
                        $stockHabitacion->lote_id = $loteConsumidoId;
                    }
                    $stockHabitacion->save();
                } else {
                    $stockHabitacion = HabitacionStock::create([
                        'habitacion_id' => $habitacion->id,
                        'producto_variante_id' => $variante->id,
                        'lote_id' => $loteConsumidoId,
                        'cantidad_ideal' => $cantidadTotal,
                        'cantidad_actual' => $cantidadTotal,
                    ]);
                }

                $resultado[] = [
                    'variante_id' => $variante->id,
                    'cantidad_asignada' => $cantidadTotal,
                    'habitacion_stock_id' => $stockHabitacion->id,
                    'lote_id' => $loteConsumidoId,
                ];
            }

            return $resultado;
        });
    }
}
