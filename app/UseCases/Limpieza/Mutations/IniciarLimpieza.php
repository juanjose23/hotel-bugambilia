<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Support\Facades\DB;

class IniciarLimpieza
{
    /**
     * Inicia la limpieza de una ejecución.
     */
    public function execute(LimpiezaEjecucion $ejecucion, ?int $colaboradorId = null, ?int $carritoId = null): void
    {
        $colaboradorId ??= auth()->id();

        DB::transaction(function () use ($ejecucion, $colaboradorId, $carritoId) {
            $ejecucion->update([
                'estado' => EstadoLimpieza::EnProgreso,
                'colaborador_id' => $colaboradorId,
                'carrito_id' => $carritoId,
                'hora_inicio' => now(),
            ]);

            $limpiable = $ejecucion->limpiable;
            if ($limpiable instanceof Habitacion) {
                $limpiable->update([
                    'estado' => EstadoHabitacion::EN_LIMPIEZA,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Limpieza,
                ]);
            }
        });
    }
}
