<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Queries;

use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Models\Habitaciones\Habitacion;
use App\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Collection;

class VerificarDiscrepanciasHabitacion
{
    /** @return Collection<int, array<string, mixed>> */
    public function execute(
        ?int $habitacionId = null,
        ?EstadoStock $filtrarPor = null,
    ): Collection {
        $query = SharedStock::with(['stockable', 'variante.producto', 'lote'])
            ->where('stockable_type', Habitacion::class);

        if ($habitacionId !== null) {
            $query->where('stockable_id', $habitacionId);
        }

        /** @var Collection<int, SharedStock> $stocks */
        $stocks = $query->get();

        /** @var array<int, array<string, mixed>> $grupo */
        $grupo = [];
        foreach ($stocks as $stock) {
            /** @var Habitacion|null $habitacion */
            $habitacion = $stock->stockable;
            $variante = $stock->variante;
            $lote = $stock->lote;

            $diferencia = (float) $stock->cantidad_actual - (float) $stock->cantidad_ideal;
            $estado = EstadoStock::calcular((float) $stock->cantidad_actual, (float) $stock->cantidad_ideal);

            if ($filtrarPor !== null && $estado !== $filtrarPor) {
                continue;
            }

            $grupo[] = [
                'id' => $stock->id,
                'habitacion_id' => $habitacion?->id,
                'habitacion_codigo' => $habitacion->codigo ?? 'N/A',
                'habitacion_nombre' => $habitacion->nombre ?? 'N/A',
                'variante_id' => $stock->producto_variante_id,
                'variante_nombre' => $variante->nombre_variante ?? 'N/A',
                'producto_nombre' => $variante !== null && $variante->producto !== null ? $variante->producto->nombre : 'N/A',
                'cantidad_ideal' => (float) $stock->cantidad_ideal,
                'cantidad_actual' => (float) $stock->cantidad_actual,
                'diferencia' => $diferencia,
                'estado' => $estado,
                'ultima_verificacion' => $stock->ultima_verificacion,
                'lote_codigo' => $lote?->getAttribute('codigo'),
            ];
        }

        return new Collection($grupo);
    }
}
