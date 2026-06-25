<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Models\Limpieza\Turno;
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
                assert(is_numeric($limpiable), 'El valor limpiable debe ser numérico en este contexto.');
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
                'estado' => EstadoLimpieza::Pendiente,
                'notas' => $notas,
            ]);

            $ubicacion = null;
            if ($instance instanceof Ubicacion) {
                $ubicacion = $instance;
            } elseif ($instance instanceof Habitacion || $instance instanceof Espacio) {
                $ubicacion = $instance->ubicacion;
            }

            $turno = null;
            $currentUbicacion = $ubicacion;
            while ($currentUbicacion) {
                $turno = Turno::where('estado', true)
                    ->whereJsonContains('carritos_ids', $currentUbicacion->id)
                    ->first();

                if ($turno) {
                    break;
                }

                $currentUbicacion = $currentUbicacion->padre;
            }

            if (! $turno) {
                $turno = Turno::where('estado', true)->first() ?: Turno::first();
            }

            if ($turno) {
                LimpiezaEjecucion::create([
                    'solicitud_id' => $solicitud->id,
                    'limpiable_type' => $modelClass,
                    'limpiable_id' => $modelId,
                    'turno_id' => $turno->id,
                    'colaborador_id' => null, // starts unassigned
                    'fecha' => now()->toDateString(),
                    'estado' => EstadoLimpieza::Pendiente,
                ]);
            }

            return $solicitud;
        });
    }
}
