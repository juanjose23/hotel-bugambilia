<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\Data\IniciarLimpiezaData;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Support\Facades\DB;

class IniciarLimpieza
{
    public function execute(IniciarLimpiezaData $dto): void
    {
        DB::transaction(function () use ($dto) {
            $record = $dto->record;
            $colaboradorOrPersonalId = $dto->colaboradorOrPersonalId;
            $carritoId = $dto->carritoId;

            $ejecucion = null;
            $solicitud = null;

            if ($record instanceof LimpiezaEjecucion) {
                $ejecucion = $record;
                $record->loadMissing('solicitud');
                $solicitud = $record->solicitud;
            } elseif ($record instanceof SolicitudLimpieza) {
                $solicitud = $record;
                $ejecucion = LimpiezaEjecucion::where('solicitud_id', $record->id)->first();
            }

            if ($ejecucion && $carritoId) {
                $isBlocked = LimpiezaEjecucion::where('estado', EstadoLimpieza::EnProgreso)
                    ->where('carrito_id', $carritoId)
                    ->where('id', '!=', $ejecucion->id)
                    ->exists();

                if ($isBlocked) {
                    throw new \Exception('El carrito seleccionado ya está siendo utilizado en otra limpieza activa.');
                }
            }

            $record->loadMissing('limpiable');
            $limpiable = $record->getRelation('limpiable');
            $estadoPrevioVal = null;
            if ($limpiable instanceof Habitacion) {
                $estadoPrevioVal = $limpiable->estado->value;

                $limpiable->update([
                    'estado' => EstadoEspacio::EN_LIMPIEZA,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Limpieza,
                ]);
            }

            if ($ejecucion) {
                $colaboradorId = null;
                if ($record instanceof LimpiezaEjecucion) {
                    $colaboradorId = $colaboradorOrPersonalId ?: auth()->user()?->persona?->colaborador?->id;
                } else {
                    $userId = $colaboradorOrPersonalId ?: auth()->id();
                    $colaboradorId = Colaborador::whereHas('persona.user', function ($query) use ($userId) {
                        $query->where('id', $userId);
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
