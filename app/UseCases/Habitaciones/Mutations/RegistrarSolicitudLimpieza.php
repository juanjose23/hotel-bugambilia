<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Habitaciones\SolicitudLimpieza;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegistrarSolicitudLimpieza
{
    /**
     * Registra una solicitud de limpieza para una habitación o espacio.
     *
     * @param  mixed  $limpiable  Instancia o ID de habitación
     * @param  int|null  $limpiableId  Opcional si el primero es un string de clase
     */
    public function execute($limpiable, ?int $limpiableId = null, string $prioridad = 'normal', ?string $notas = null): SolicitudLimpieza
    {
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
                // Retrocompatibilidad con test: si es entero, asumimos Habitacion
                assert(is_numeric($limpiable), 'El valor limpiable debe ser numérico en este contexto.');
                $modelId = (int) $limpiable;
                $modelClass = Habitacion::class;
                $instance = Habitacion::findOrFail($modelId);
            }

            // Cambiar estado según el tipo
            if ($instance instanceof Habitacion) {
                $instance->update([
                    'estado' => EstadoHabitacion::SUCIA,
                ]);
            } elseif ($instance instanceof Espacio) {
                $instance->update([
                    'estado' => EstadoEspacio::Limpieza,
                ]);
            }

            // Crear solicitud de limpieza pendiente
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
