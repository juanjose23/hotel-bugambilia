<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Queries;

use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Models\Habitaciones\HabitacionStock;
use Illuminate\Support\Collection;

class VerificarDiscrepanciasHabitacion
{
    /** @return Collection<int, array<string, mixed>> */
    public function execute(
        ?int $habitacionId = null,
        ?EstadoStock $filtrarPor = null,
    ): Collection {
        $query = HabitacionStock::with(['habitacion', 'variante.producto', 'lote']);

        if ($habitacionId !== null) {
            $query->where('habitacion_id', $habitacionId);
        }

        /** @var Collection<int, HabitacionStock> $stocks */
        $stocks = $query->get();

        /** @var array<int, array<string, mixed>> $grupo */
        $grupo = [];
        foreach ($stocks as $stock) {
            $habitacion = $stock->habitacion;
            $variante = $stock->variante;
            $lote = $stock->lote;

            $diferencia = (float) $stock->cantidad_actual - (float) $stock->cantidad_ideal;
            $estado = EstadoStock::calcular((float) $stock->cantidad_actual, (float) $stock->cantidad_ideal);

            if ($filtrarPor !== null && $estado !== $filtrarPor) {
                continue;
            }

            $grupo[] = [
                'id' => $stock->id,
                'habitacion_id' => $stock->habitacion_id,
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
