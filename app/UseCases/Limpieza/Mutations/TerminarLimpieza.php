<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Stock as InventarioStock;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Facades\DB;

class TerminarLimpieza
{
    /**
     * Termina la limpieza de una habitación o espacio.
     *
     * @param  LimpiezaEjecucion|SolicitudLimpieza  $record
     * @param  array<string, bool>  $checklist
     * @param  array<int, float>  $consumos
     */
    public function execute($record, array $checklist = [], ?string $observaciones = null, array $consumos = []): void
    {
        DB::transaction(function () use ($record, $checklist, $observaciones, $consumos) {
            $ejecucion = null;
            $solicitud = null;

            if ($record instanceof LimpiezaEjecucion) {
                $ejecucion = $record;
                $solicitud = $record->solicitud;
            } elseif ($record instanceof SolicitudLimpieza) {
                $solicitud = $record;
                $ejecucion = LimpiezaEjecucion::where('solicitud_id', $record->id)->first();
            }

            // 1. Update LimpiezaEjecucion
            if ($ejecucion) {
                $hasDiscrepancy = false;
                foreach ($checklist as $task => $completed) {
                    if (! $completed) {
                        $hasDiscrepancy = true;
                    }
                }
                $estado = $hasDiscrepancy ? EstadoLimpieza::CompletadaConDiscrepancia : EstadoLimpieza::Completada;

                $ejecucion->update([
                    'estado' => $estado,
                    'hora_fin' => now()->format('H:i:s'),
                    'detalles_checklist' => $checklist,
                    'observaciones' => $observaciones,
                    'consumos' => $consumos,
                ]);

                // Register Stock Consumption if cart is present
                if ($ejecucion->carrito_id && ! empty($consumos)) {
                    $tipoDestino = match ($ejecucion->limpiable_type) {
                        Habitacion::class => 'habitacion',
                        Espacio::class => 'espacio',
                        Ubicacion::class => 'ubicacion',
                        default => null,
                    };

                    if ($tipoDestino) {
                        $items = [];
                        foreach ($consumos as $varianteId => $cantidad) {
                            if ($cantidad > 0) {
                                $items[] = [
                                    'producto_variante_id' => (int) $varianteId,
                                    'cantidad' => (float) $cantidad,
                                ];
                            }
                        }

                        if (! empty($items)) {
                            app(ReabastecerUbicacion::class)->execute(
                                tipoDestino: $tipoDestino,
                                destinoId: $ejecucion->limpiable_id,
                                items: $items,
                                bodegaOrigenId: $ejecucion->carrito_id,
                                creadoPorId: auth()->id() !== null ? (int) auth()->id() : null,
                                notas: "Consumo registrado al completar ejecución de limpieza #{$ejecucion->id}."
                            );
                        }
                    }
                }
            }

            // 2. Update SolicitudLimpieza
            if ($solicitud) {
                $solicitud->update([
                    'estado' => EstadoLimpieza::Completada,
                ]);
            }

            // 3. Update physical state
            $limpiable = $record->limpiable;
            if ($limpiable instanceof Habitacion) {
                $nuevoEstado = EstadoHabitacion::DISPONIBLE;

                if ($ejecucion && $ejecucion->estado_previo !== null) {
                    $prev = EstadoHabitacion::fromValue($ejecucion->estado_previo);
                    if ($prev && in_array($prev, [EstadoHabitacion::Ocupada, EstadoHabitacion::Mantenimiento], true)) {
                        $nuevoEstado = $prev;
                    }
                }

                $limpiable->update([
                    'estado' => $nuevoEstado,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Disponible,
                ]);
            }

            if ($ejecucion && ! empty($consumos) && $ejecucion->carrito_id) {
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
