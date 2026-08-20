<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\BusinessLogic\Limpieza\Data\EnviarALavanderiaData;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class EnviarALavanderia
{
    public function execute(EnviarALavanderiaData $dto): void
    {
        if (empty($dto->items)) {
            throw new \InvalidArgumentException('Debe seleccionar al menos un item para enviar a lavandería.');
        }

        $stockableTypeMap = [
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
        ];

        DB::transaction(function () use ($dto, $stockableTypeMap) {
            foreach ($dto->items as $item) {
                if (! array_key_exists($item->tipo, $stockableTypeMap)) {
                    throw new \InvalidArgumentException("Tipo de stock inválido: {$item->tipo}");
                }

                $stock = SharedStock::with(['lote', 'variante.producto'])
                    ->whereKey($item->stockId)
                    ->lockForUpdate()
                    ->firstOrFail();
                $origenNombre = ucfirst($item->tipo)." #{$stock->stockable_id}";
                $productoId = $stock->variante?->producto_id;

                if ($productoId === null) {
                    throw new \RuntimeException('El blanco seleccionado no tiene producto asociado.');
                }

                $cantidadEnviar = min((float) $stock->cantidad_actual, $item->cantidad);
                if ($cantidadEnviar <= 0) {
                    continue;
                }

                $stock->cantidad_actual -= $cantidadEnviar;
                $stock->save();

                $stockLavanderia = Stock::where([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $stock->producto_variante_id,
                    'lote_id' => $stock->lote_id,
                    'ubicacion_id' => $dto->ubicacionLavanderiaId,
                ])->lockForUpdate()->first();

                if ($stockLavanderia) {
                    $stockLavanderia->cantidad += $cantidadEnviar;
                    $stockLavanderia->save();
                } else {
                    Stock::create([
                        'producto_id' => $productoId,
                        'producto_variante_id' => $stock->producto_variante_id,
                        'lote_id' => $stock->lote_id,
                        'ubicacion_id' => $dto->ubicacionLavanderiaId,
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
                    'ubicacion_destino_id' => $dto->ubicacionLavanderiaId,
                    'documento_tipo' => 'lavanderia',
                    'referencia' => "Envío a lavandería desde {$origenNombre}",
                    'creado_por_id' => $dto->creadoPorId,
                    'notas' => $dto->notas,
                ]);
            }
        });
    }
}
