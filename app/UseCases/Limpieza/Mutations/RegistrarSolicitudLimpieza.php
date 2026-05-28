<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegistrarSolicitudLimpieza
{
    /**
     * @param  mixed  $limpiable
     */
    public function execute(
        $limpiable,
        ?int $limpiableId = null,
        string $prioridad = 'normal',
        ?string $notas = null,
    ): SolicitudLimpieza {
        return DB::transaction(function () use ($limpiable, $limpiableId, $prioridad, $notas) {
            if ($limpiable instanceof Model) {
                $instance = $limpiable;
                $modelClass = get_class($instance);
                $modelId = $instance->getKey();
            } elseif (is_string($limpiable) && $limpiableId !== null) {
                $modelClass = $limpiable;
                $modelId = $limpiableId;
                $instance = $modelClass::findOrFail($modelId);
            } else {
                $modelId = (int) $limpiable;
                $modelClass = Habitacion::class;
                $instance = Habitacion::findOrFail($modelId);
            }

            if ($instance instanceof Habitacion) {
                $instance->update([
                    'estado' => EstadoHabitacion::SUCIA,
                ]);
            } elseif ($instance instanceof Espacio) {
                $instance->update([
                    'estado' => EstadoEspacio::Limpieza,
                ]);
            }

            $solicitud = SolicitudLimpieza::create([
                'limpiable_type' => $modelClass,
                'limpiable_id' => $modelId,
                'prioridad' => $prioridad,
                'estado' => 'pendiente',
                'notas' => $notas,
            ]);

            return $solicitud;
        });
    }
}
