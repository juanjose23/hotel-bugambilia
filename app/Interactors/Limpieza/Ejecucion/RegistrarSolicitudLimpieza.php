<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\Turno;
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
                    'estado' => EstadoEspacio::SUCIA,
                ]);
            }
            // Espacio (mesa) no cambia estado aquí: el caller (CerrarPedidoMesa) ya lo establece vía CambiarEstadoMesa

            $solicitudExistente = SolicitudLimpieza::query()
                ->where('limpiable_type', $modelClass)
                ->where('limpiable_id', $modelId)
                ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
                ->first();

            if ($solicitudExistente !== null) {
                return $solicitudExistente;
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
                $instance->loadMissing('ubicacion');
                $ubicacion = $instance->ubicacion;
            }

            $turno = null;

            if ($instance instanceof Espacio || $modelClass === Espacio::class) {
                $turno = Turno::where('estado', true)
                    ->where(function ($q) {
                        $q->where('nombre', 'like', '%restaurante%')
                            ->orWhere('nombre', 'like', '%comedor%');
                    })
                    ->first();

                if (! $turno) {
                    $turno = Turno::query()->firstOrCreate(
                        ['nombre' => 'Turno Restaurante'],
                        [
                            'hora_inicio' => '06:00:00',
                            'hora_fin' => '23:00:00',
                            'estado' => true,
                        ]
                    );
                }
            }

            if (! $turno) {
                $currentUbicacion = $ubicacion;
                while ($currentUbicacion) {
                    $turno = Turno::where('estado', true)
                        ->whereHas('carritos', fn ($q) => $q->where('ubicacion_id', $currentUbicacion->id))
                        ->first();

                    if ($turno) {
                        break;
                    }

                    $currentUbicacion->loadMissing('padre');
                    $currentUbicacion = $currentUbicacion->padre;
                }
            }

            if (! $turno) {
                $turno = Turno::where('estado', true)->first() ?: Turno::first();
            }

            if (! $turno) {
                $turno = Turno::query()->create([
                    'nombre' => 'Turno Mañana',
                    'hora_inicio' => '07:00:00',
                    'hora_fin' => '15:00:00',
                    'estado' => true,
                ]);
            }

            LimpiezaEjecucion::create([
                'solicitud_id' => $solicitud->id,
                'limpiable_type' => $modelClass,
                'limpiable_id' => $modelId,
                'turno_id' => $turno->id,
                'colaborador_id' => null,
                'fecha' => now()->toDateString(),
                'estado' => EstadoLimpieza::Pendiente,
            ]);

            return $solicitud;
        });
    }
}
