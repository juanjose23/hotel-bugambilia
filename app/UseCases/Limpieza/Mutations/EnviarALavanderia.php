<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Models\Espacios\EspacioStock;
use App\Models\Habitaciones\HabitacionStock;
use App\Models\Inventario\MovimientoStock;
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
                if ($item['tipo'] === 'habitacion') {
                    $stock = HabitacionStock::findOrFail($item['stock_id']);
                    $origenNombre = "Habitacion #{$stock->habitacion_id}";

                    $cantidadEnviar = min((float) $stock->cantidad_actual, (float) $item['cantidad']);
                    if ($cantidadEnviar <= 0) {
                        continue;
                    }

                    $stock->cantidad_actual -= $cantidadEnviar;
                    $stock->save();
                } elseif ($item['tipo'] === 'espacio') {
                    $stock = EspacioStock::findOrFail($item['stock_id']);
                    $origenNombre = "Espacio #{$stock->espacio_id}";

                    $cantidadEnviar = min((float) $stock->cantidad_actual, (float) $item['cantidad']);
                    if ($cantidadEnviar <= 0) {
                        continue;
                    }

                    $stock->cantidad_actual -= $cantidadEnviar;
                    $stock->save();
                } else {
                    throw new \InvalidArgumentException("Tipo de stock inválido: {$item['tipo']}");
                }

                MovimientoStock::create([
                    'tipo' => 'TRASLADO_LAVANDERIA',
                    'producto_id' => $stock->variante->producto_id ?? $stock->producto_variante_id,
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
