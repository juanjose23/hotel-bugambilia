<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Support\Facades\DB;

class TerminarLimpieza
{
    /**
     * Termina la limpieza de una habitación o espacio.
     */
    public function execute(SolicitudLimpieza $solicitud): void
    {
        DB::transaction(function () use ($solicitud) {
            $solicitud->update([
                'estado' => 'completada',
            ]);

            $limpiable = $solicitud->limpiable;
            if ($limpiable instanceof Habitacion) {
                $limpiable->update([
                    'estado' => EstadoHabitacion::DISPONIBLE,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Disponible,
                ]);
            }
        });
    }
}
