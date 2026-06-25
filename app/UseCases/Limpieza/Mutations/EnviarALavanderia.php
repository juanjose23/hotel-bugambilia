<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Models\Inventario\MovimientoStock;
use App\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class EnviarALavanderia
{
    /**
     * Envía items textiles desde una ubicación operativa a la lavandería.
     *
     * @param  array<int, array{stock_id: int, tipo: 'habitacion'|'espacio', cantidad: float}>  $items
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

        DB::transaction(function () use ($items, $ubicacionLavanderiaId, $creadoPorId, $notas) {
            foreach ($items as $item) {
                if (! in_array($item['tipo'], ['habitacion', 'espacio'], true)) {
                    throw new \InvalidArgumentException("Tipo de stock inválido: {$item['tipo']}");
                }

                $stock = SharedStock::findOrFail($item['stock_id']);
                $origenNombre = ucfirst($item['tipo'])." #{$stock->stockable_id}";

                $cantidadEnviar = min((float) $stock->cantidad_actual, (float) $item['cantidad']);
                if ($cantidadEnviar <= 0) {
                    continue;
                }

                $stock->cantidad_actual -= $cantidadEnviar;
                $stock->save();

                MovimientoStock::create([
                    'tipo' => 'TRASLADO_LAVANDERIA',
                    'producto_id' => $stock->variante->producto_id ?? 0,
                    'cantidad' => -$cantidadEnviar,
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
