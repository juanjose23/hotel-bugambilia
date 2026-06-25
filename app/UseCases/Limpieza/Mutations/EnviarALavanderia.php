<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class EnviarALavanderia
{
    /**
     * Envía items textiles desde una ubicación operativa a la lavandería.
     *
     * @param  array<int, array{stock_id: int, tipo: 'habitacion'|'espacio'|'ubicacion', cantidad: float}>  $items
     */
    public function execute(
        array $items,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId = null,
        ?string $notas = null,
    ): void {
        if (empty($items)) {
            throw new \InvalidArgumentException('Debe seleccionar al menos un item para enviar a lavandería.');
        }

        $stockableTypeMap = [
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
        ];

        DB::transaction(function () use ($items, $ubicacionLavanderiaId, $creadoPorId, $notas, $stockableTypeMap) {
            foreach ($items as $item) {
                if (! array_key_exists($item['tipo'], $stockableTypeMap)) {
                    throw new \InvalidArgumentException("Tipo de stock inválido: {$item['tipo']}");
                }

                $stock = SharedStock::with('lote')->findOrFail($item['stock_id']);
                $origenNombre = ucfirst($item['tipo'])." #{$stock->stockable_id}";

                $cantidadEnviar = min((float) $stock->cantidad_actual, (float) $item['cantidad']);
                if ($cantidadEnviar <= 0) {
                    continue;
                }

                $stock->cantidad_actual -= $cantidadEnviar;
                $stock->save();

                // Add physical stock to the laundry location so it can be tracked
                $stockLavanderia = Stock::where([
                    'producto_id' => $stock->variante->producto_id ?? $stock->producto_variante_id,
                    'producto_variante_id' => $stock->producto_variante_id,
                    'lote_id' => $stock->lote_id,
                    'ubicacion_id' => $ubicacionLavanderiaId,
                ])->first();

                if ($stockLavanderia) {
                    $stockLavanderia->cantidad += $cantidadEnviar;
                    $stockLavanderia->save();
                } else {
                    Stock::create([
                        'producto_id' => $stock->variante->producto_id ?? $stock->producto_variante_id,
                        'producto_variante_id' => $stock->producto_variante_id,
                        'lote_id' => $stock->lote_id,
                        'ubicacion_id' => $ubicacionLavanderiaId,
                        'cantidad' => $cantidadEnviar,
                    ]);
                }

                $costoUnitarioMov = $stock->lote?->costo_unitario;
                $costoTotalMov = $costoUnitarioMov !== null
                    ? $costoUnitarioMov * $cantidadEnviar
                    : null;

                MovimientoStock::create([
                    'tipo' => 'TRASLADO_LAVANDERIA',
                    'producto_id' => $stock->variante->producto_id ?? 0,
                    'cantidad' => -$cantidadEnviar,
                    'costo_unitario' => $costoUnitarioMov,
                    'costo_total' => $costoTotalMov,
                    'ubicacion_origen_id' => null,
                    'ubicacion_destino_id' => $ubicacionLavanderiaId,
                    'documento_tipo' => 'lavanderia',
                    'referencia' => "Envío a lavandería desde {$origenNombre}",
                    'creado_por_id' => $creadoPorId,
                    'notas' => $notas,
                ]);
            }
        });
    }
}
