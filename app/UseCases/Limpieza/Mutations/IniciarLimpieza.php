<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Support\Facades\DB;

class IniciarLimpieza
{
    /**
     * Inicia la limpieza de una habitación o espacio.
     */
    public function execute(SolicitudLimpieza $solicitud, ?int $personalId = null): void
    {
        DB::transaction(function () use ($solicitud, $personalId) {
            $solicitud->update([
                'estado' => 'en_progreso',
                'personal_id' => $personalId ?: auth()->id(),
            ]);

            $limpiable = $solicitud->limpiable;
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
