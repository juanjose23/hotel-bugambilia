<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Reposiciones\Mutations;

use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\ParStock;
use App\Models\Inventario\Reposicion;
use App\Models\Inventario\ReposicionItem;
use App\Models\Inventario\Stock;
use App\UseCases\Inventario\Services\PutawayPolicy;
use Illuminate\Support\Facades\DB;

class GenerarReposicionesBodega
{
    /**
     * Compara los stocks actuales de las bodegas con su configuración de Par Stock
     * y genera órdenes de reposición desde el Almacén General para aquellas que estén por debajo del mínimo.
     *
     * @return array<int, Reposicion>
     */
    public function execute(?int $creadoPorId = null): array
    {
        return DB::transaction(function () use ($creadoPorId) {
            // 1. Obtener el Almacén General (origen por defecto)
            $almacenGeneral = Ubicacion::where('tipo', 'almacen')->where('estado', 1)->first();
            if (! $almacenGeneral) {
                try {
                    $almacenGeneral = PutawayPolicy::sugerirUbicacion();
                } catch (\RuntimeException $e) {
                    // Si no hay ninguna ubicación sugerida, no podemos proceder
                    return [];
                }
            }

            // 2. Obtener todas las configuraciones de Par Stock
            $parStocks = ParStock::all();
            $reposicionesGeneradas = [];

            // Agrupar por bodega destino (ubicacion_id)
            $agrupados = $parStocks->groupBy('ubicacion_id');

            foreach ($agrupados as $destinoId => $pars) {
                $destinoId = (int) $destinoId;

                // No reponer si el destino es el mismo Almacén General
                if ($destinoId === $almacenGeneral->id) {
                    continue;
                }

                // Encontrar o inicializar la orden de reposición pendiente para este destino
                $reposicion = Reposicion::where([
                    'origen_id' => $almacenGeneral->id,
                    'destino_id' => $destinoId,
                    'estado' => 'pendiente',
                ])->first();

                $itemsACrear = [];

                foreach ($pars as $par) {
                    $productoId = (int) $par->producto_id;
                    $varianteId = $par->producto_variante_id ? (int) $par->producto_variante_id : null;
                    $minimo = (float) $par->stock_minimo;
                    $objetivo = (float) $par->stock_objetivo;

                    // Calcular stock actual en la bodega destino
                    $actual = (float) Stock::where([
                        'producto_id' => $productoId,
                        'ubicacion_id' => $destinoId,
                    ])->when($varianteId !== null, function ($q) use ($varianteId) {
                        $q->where('producto_variante_id', $varianteId);
                    })->sum('cantidad');

                    // Si está por debajo del mínimo, calcular reposición
                    if ($actual < $minimo) {
                        $cantidadRequerida = $objetivo - $actual;
                        if ($cantidadRequerida > 0) {
                            $itemsACrear[] = [
                                'producto_id' => $productoId,
                                'producto_variante_id' => $varianteId,
                                'cantidad' => $cantidadRequerida,
                            ];
                        }
                    }
                }

                // Si hay artículos que requieren reposición, crear la orden
                if (! empty($itemsACrear)) {
                    if (! $reposicion) {
                        $codigo = 'REP-'.now()->format('Ymd').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $reposicion = Reposicion::create([
                            'codigo' => $codigo,
                            'origen_id' => $almacenGeneral->id,
                            'destino_id' => $destinoId,
                            'estado' => 'pendiente',
                            'creado_por_id' => $creadoPorId,
                            'notas' => 'Generado automáticamente por sistema de Par Stock.',
                        ]);
                    }

                    foreach ($itemsACrear as $item) {
                        // Evitar duplicar el mismo item en la misma reposición
                        ReposicionItem::updateOrCreate([
                            'reposicion_id' => $reposicion->id,
                            'producto_id' => $item['producto_id'],
                            'producto_variante_id' => $item['producto_variante_id'],
                        ], [
                            'cantidad_solicitada' => $item['cantidad'],
                            'cantidad_surtida' => 0.0,
                        ]);
                    }

                    $reposicionesGeneradas[] = $reposicion;
                }
            }

            return $reposicionesGeneradas;
        });
    }
}
