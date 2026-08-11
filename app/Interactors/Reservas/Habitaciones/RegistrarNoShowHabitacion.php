<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\RegistrarNoShowData;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\ReservaHabitacionNoShow;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarNoShowHabitacion
{
    public function ejecutar(RegistrarNoShowData $data): Reserva
    {
        return DB::transaction(function () use ($data): Reserva {
            $reserva = Reserva::query()->where('id', $data->reservaId)->lockForUpdate()->firstOrFail();

            if (! in_array($reserva->estado, [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA], true)) {
                throw new DomainException("No se puede registrar No-Show en una reserva en estado {$reserva->estado->getLabel()}.");
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
                'estado' => EstadoReserva::NO_SHOW,
            ]);

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => EstadoReserva::NO_SHOW,
                'motivo' => 'Registro de No-Show: '.($data->motivo ?? 'Huésped no se presentó'),
                'usuario_id' => $data->usuarioId,
            ]);

            ReservaHabitacionNoShow::dispatch($reserva, $data->motivo);

            return $reserva->refresh();
        });
    }
}
