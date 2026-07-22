<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;

class ActualizadorEstadoEspacioLimpieza
{
    public function actualizar(LimpiezaEjecucion|SolicitudLimpieza $record, ?LimpiezaEjecucion $ejecucion): void
    {
        $record->loadMissing('limpiable');
        $limpiable = $record->limpiable;

        if ($limpiable instanceof Habitacion) {
            $nuevoEstado = EstadoEspacio::DISPONIBLE;

            if ($ejecucion && $ejecucion->estado_previo !== null) {
                $prev = EstadoEspacio::fromValue($ejecucion->estado_previo);
                if ($prev && in_array($prev, [EstadoEspacio::Ocupada, EstadoEspacio::Mantenimiento], true)) {
                    $nuevoEstado = $prev;
                }
            }

            $limpiable->update(['estado' => $nuevoEstado]);
        } elseif ($limpiable instanceof Espacio) {
            $limpiable->update(['estado' => EstadoEspacio::Disponible]);
        }
    }
}
