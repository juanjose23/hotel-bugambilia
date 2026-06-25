<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Mutations;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Colaboradores\Colaborador;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Support\Facades\DB;

class IniciarLimpieza
{
    /**
     * Inicia la limpieza de una habitación o espacio.
     *
     * @param  LimpiezaEjecucion|SolicitudLimpieza  $record
     */
    public function execute($record, ?int $colaboradorOrPersonalId = null, ?int $carritoId = null): void
    {
        DB::transaction(function () use ($record, $colaboradorOrPersonalId, $carritoId) {
            $ejecucion = null;
            $solicitud = null;

            if ($record instanceof LimpiezaEjecucion) {
                $ejecucion = $record;
                $solicitud = $record->solicitud;
            } elseif ($record instanceof SolicitudLimpieza) {
                $solicitud = $record;
                $ejecucion = LimpiezaEjecucion::where('solicitud_id', $record->id)->first();
            }

            if ($ejecucion) {
                if (! $carritoId) {
                    throw new \Exception('Debe seleccionar un carrito de limpieza para iniciar.');
                }

                // Check if another active execution is using this cart
                $isBlocked = LimpiezaEjecucion::where('estado', EstadoLimpieza::EnProgreso)
                    ->where('carrito_id', $carritoId)
                    ->where('id', '!=', $ejecucion->id)
                    ->exists();

                if ($isBlocked) {
                    throw new \Exception('El carrito seleccionado ya está siendo utilizado en otra limpieza activa.');
                }
            }

            // 3. Update physical state and record prior state
            $limpiable = $record->limpiable;
            $estadoPrevioVal = null;
            if ($limpiable instanceof Habitacion) {
                $estadoPrevioVal = $limpiable->estado instanceof EstadoHabitacion
                    ? $limpiable->estado->value
                    : (int) $limpiable->estado;

                $limpiable->update([
                    'estado' => EstadoHabitacion::EN_LIMPIEZA,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Limpieza,
                ]);
            }

            // 1. Update LimpiezaEjecucion
            if ($ejecucion) {
                $colaboradorId = null;
                if ($record instanceof LimpiezaEjecucion) {
                    $colaboradorId = $colaboradorOrPersonalId ?: auth()->user()?->persona?->colaborador?->id;
                } else {
                    $userId = $colaboradorOrPersonalId ?: auth()->id();
                    $colaboradorId = Colaborador::whereHas('persona', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })->value('id');
                }

                $ejecucion->update([
                    'estado' => EstadoLimpieza::EnProgreso,
                    'colaborador_id' => $colaboradorId,
                    'carrito_id' => $carritoId,
                    'hora_inicio' => now()->format('H:i:s'),
                    'estado_previo' => $estadoPrevioVal,
                ]);
            }

            // 2. Update SolicitudLimpieza
            if ($solicitud) {
                $userId = null;
                if ($record instanceof SolicitudLimpieza) {
                    $userId = $colaboradorOrPersonalId ?: auth()->id();
                } else {
                    $colabId = $colaboradorOrPersonalId ?: auth()->user()?->persona?->colaborador?->id;
                    if ($colabId) {
                        $colaborador = Colaborador::with('persona.user')->find($colabId);
                        $userId = $colaborador?->persona?->user?->id;
                    }
                    if (! $userId) {
                        $userId = auth()->id();
                    }
                }

                $solicitud->update([
                    'estado' => EstadoLimpieza::EnProgreso,
                    'personal_id' => $userId,
                ]);
            }
        });
    }
}
