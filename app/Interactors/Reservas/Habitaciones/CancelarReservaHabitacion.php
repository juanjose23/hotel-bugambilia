<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\ReservaHabitacionCancelada;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class CancelarReservaHabitacion
{
    public function ejecutar(CancelarReservaHabitacionData $data): Reserva
    {
        return DB::transaction(function () use ($data): Reserva {
            $reserva = Reserva::query()->where('id', $data->reservaId)->lockForUpdate()->firstOrFail();

            if (in_array($reserva->estado, [EstadoReserva::CANCELADA, EstadoReserva::CHECKED_OUT, EstadoReserva::NO_SHOW], true)) {
                throw new DomainException("La reserva #{$reserva->codigo_reserva} no se puede cancelar desde su estado actual ({$reserva->estado->getLabel()}).");
            }

            $tieneEstanciaActiva = $reserva->estancias()
                ->where('estado', EstadoEstancia::ACTIVA)
                ->exists();

            if ($tieneEstanciaActiva) {
                throw new DomainException("No se puede cancelar la reserva #{$reserva->codigo_reserva} porque posee estancias activas.");
            }

            $estadoAnterior = $reserva->estado;

            foreach ($reserva->detalles as $detalle) {
                /** @var ReservaDetalle $detalle */
                $detalle->update([
                    'estado' => EstadoReservaDetalle::CANCELADO,
                    'cancelado_at' => now(),
                ]);
            }

            $reserva->update([
                'estado' => EstadoReserva::CANCELADA,
            ]);

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => EstadoReserva::CANCELADA,
                'motivo' => 'Cancelación: '.$data->motivo,
                'usuario_id' => $data->usuarioId,
            ]);

            ReservaHabitacionCancelada::dispatch($reserva, $data->motivo, $data->montoPenalizacion);

            return $reserva->refresh();
        });
    }
}
