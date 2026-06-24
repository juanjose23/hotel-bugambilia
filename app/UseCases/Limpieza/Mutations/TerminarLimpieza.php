<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Stock as InventarioStock;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class TerminarLimpieza
{
    /**
     * Termina la limpieza de una ejecución.
     *
     * @param  array<string, bool>  $checklist
     * @param  array<int, float>  $consumos
     */
    public function execute(LimpiezaEjecucion $ejecucion, array $checklist = [], string $observaciones = '', array $consumos = []): void
    {
        DB::transaction(function () use ($ejecucion, $checklist, $observaciones, $consumos) {
            $hasDiscrepancia = in_array(false, $checklist, true);
            $estadoFinal = $hasDiscrepancia ? EstadoLimpieza::CompletadaConDiscrepancia : EstadoLimpieza::Completada;

            $ejecucion->update([
                'estado' => $estadoFinal,
                'detalles_checklist' => ! empty($checklist) ? $checklist : null,
                'observaciones' => $observaciones ?: null,
                'consumos' => ! empty($consumos) ? $consumos : null,
                'hora_fin' => now(),
            ]);

            $limpiable = $ejecucion->limpiable;
            if ($limpiable instanceof Habitacion) {
                $limpiable->update([
                    'estado' => EstadoHabitacion::DISPONIBLE,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Disponible,
                ]);
            }

            if (! empty($consumos) && $ejecucion->carrito_id) {
                foreach ($consumos as $varianteId => $cantidad) {
                    $cartStock = InventarioStock::where('ubicacion_id', $ejecucion->carrito_id)
                        ->where('producto_variante_id', $varianteId)
                        ->first();

                    if ($cartStock) {
                        $cartStock->cantidad -= $cantidad;
                        if ($cartStock->cantidad <= 0.0) {
                            $cartStock->delete();
                        } else {
                            $cartStock->save();
                        }
                    }

                    if ($limpiable) {
                        $destStock = SharedStock::firstOrNew([
                            'stockable_type' => $limpiable::class,
                            'stockable_id' => $limpiable->getKey(),
                            'producto_variante_id' => $varianteId,
                        ]);
                        $destStock->cantidad_actual = ($destStock->cantidad_actual ?? 0) + $cantidad;
                        if (! $destStock->cantidad_ideal) {
                            $destStock->cantidad_ideal = $destStock->cantidad_actual;
                        }
                        $destStock->save();
                    }
                }
            }
        });
    }
}
