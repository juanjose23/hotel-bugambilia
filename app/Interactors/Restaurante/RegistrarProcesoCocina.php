<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Facades\DB;

final class RegistrarProcesoCocina
{
    /**
     * Registra un proceso de cocina basado en la receta de un plato.
     * El costo se obtiene del Stock en Cocina Restaurante → Lote → costo_unitario.
     *
     * @param  array{codigo: string, plato_id: int, cantidad_platos: int, realizado_por?: int|null, observaciones?: string|null}  $data
     */
    public function ejecutar(array $data): ProcesoCocina
    {
        return DB::transaction(function () use ($data) {
            $plato = Plato::with('receta')->findOrFail($data['plato_id']);
            $productoReceta = $plato->receta;

            if (! $productoReceta) {
                throw new \RuntimeException("El plato [{$plato->nombre}] no tiene una receta asociada.");
            }

            $cantidadPlatos = max($data['cantidad_platos'], 1);

            $ingredientes = ProductoKit::with(['variante.producto', 'productoPadre'])
                ->where('producto_padre_id', $productoReceta->id)
                ->get();

            $cocinaId = Ubicacion::where('nombre', 'Cocina Restaurante')->first()?->id;

            $costoTotal = 0.0;
            $itemsData = [];

            foreach ($ingredientes as $ingrediente) {
                $variante = $ingrediente->variante;
                $productoDestino = $variante?->producto;
                $cantidadIngrediente = (float) $ingrediente->cantidad * $cantidadPlatos;

                $costoUnitario = 0.0;
                if ($cocinaId && $variante) {
                    $stock = Stock::with('lote')
                        ->where('stockable_type', Ubicacion::class)
                        ->where('stockable_id', $cocinaId)
                        ->where('producto_variante_id', $variante->id)
                        ->where('cantidad_actual', '>', 0)
                        ->first();

                    if ($stock && $stock->lote?->costo_unitario) {
                        $costoUnitario = (float) $stock->lote->costo_unitario;
                    }
                }

                $costoAsignado = round($costoUnitario * $cantidadIngrediente, 2);
                $costoTotal += $costoAsignado;

                $itemsData[] = [
                    'ingrediente' => $ingrediente,
                    'variante' => $variante,
                    'productoDestino' => $productoDestino,
                    'cantidadIngrediente' => $cantidadIngrediente,
                    'costoUnitario' => $costoUnitario,
                    'costoAsignado' => $costoAsignado,
                ];
            }

            /** @var ProcesoCocina $proceso */
            $proceso = ProcesoCocina::create([
                'codigo' => $data['codigo'],
                'plato_id' => $plato->id,
                'cantidad_platos' => $cantidadPlatos,
                'producto_origen_id' => $productoReceta->id,
                'cantidad_procesada' => $cantidadPlatos,
                'costo_total' => $costoTotal,
                'realizado_por' => $data['realizado_por'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $ingrediente = $item['ingrediente'];
                $variante = $item['variante'];
                $productoDestino = $item['productoDestino'];
                $cantidadIngrediente = $item['cantidadIngrediente'];

                $proceso->items()->create([
                    'producto_destino_id' => $productoDestino !== null ? $productoDestino->id : $ingrediente->producto_padre_id,
                    'variante_destino_id' => $variante?->id,
                    'cantidad' => $cantidadIngrediente,
                    'peso_unitario' => $variante?->peso,
                    'peso_total' => $cantidadIngrediente,
                    'costo_asignado' => $item['costoAsignado'],
                    'es_merma' => false,
                    'ubicacion_destino_id' => null,
                ]);

                if ($cocinaId) {
                    $stockExistente = Stock::where('stockable_type', Ubicacion::class)
                        ->where('stockable_id', $cocinaId)
                        ->where('producto_variante_id', $variante?->id)
                        ->first();

                    if ($stockExistente) {
                        $stockExistente->decrement('cantidad_actual', $cantidadIngrediente);
                    }

                    MovimientoStock::create([
                        'producto_id' => $productoDestino?->id,
                        'producto_variante_id' => $variante?->id,
                        'tipo' => 'CONSUMO',
                        'cantidad' => $cantidadIngrediente,
                        'costo_unitario' => $item['costoUnitario'],
                        'fecha' => now(),
                    ]);
                }
            }

            return $proceso;
        });
    }
}
