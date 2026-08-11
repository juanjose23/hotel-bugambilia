<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\ConfirmarReservaHabitacionData;
use App\BusinessLogic\Reservas\RecalcularEstadoReservaHabitacion;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\BusinessLogic\Reservas\ValidarPoliticaPagoReserva;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\ReservaHabitacionConfirmada;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarReservaHabitacion
{
    public function __construct(
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private RecalcularEstadoReservaHabitacion $recalcularEstado,
        private ValidarPoliticaPagoReserva $validarPoliticaPago = new ValidarPoliticaPagoReserva,
    ) {}

    public function ejecutar(ConfirmarReservaHabitacionData $data): Reserva
    {
        return DB::transaction(function () use ($data): Reserva {
            $reserva = Reserva::query()->where('id', $data->reservaId)->lockForUpdate()->firstOrFail();

            if ($reserva->estado !== EstadoReserva::PENDIENTE) {
                throw new DomainException("La reserva #{$reserva->codigo_reserva} no se encuentra en estado pendiente.");
            }

            // Validar política de pago (e.g. 50% abono / 100% pago completo)
            $this->validarPoliticaPago->validarMontoParaConfirmacion($reserva);

            $detallesPrincipales = $reserva->detalles()->whereNull('parent_id')->get();
            $recursosIds = $detallesPrincipales->pluck('reservable_id')
                ->map(static fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
                ->all();

            $fechaCheckIn = $reserva->fecha_check_in;
            $fechaCheckOut = $reserva->fecha_check_out;

            if ($fechaCheckIn === null || $fechaCheckOut === null) {
                throw new DomainException("La reserva #{$reserva->codigo_reserva} no tiene un periodo de estancia definido.");
            }

            // Re-validate availability for all room details
            $this->validarDisponibilidad->validarDisponibilidad(
                fechaCheckIn: $fechaCheckIn,
                fechaCheckOut: $fechaCheckOut,
                recursosReservablesIds: $recursosIds,
                adultos: $reserva->adultos,
                ninos: $reserva->ninos,
                excluirDetalleId: $detallesPrincipales->first()?->id,
            );

            $estadoAnterior = $reserva->estado;

            foreach ($detallesPrincipales as $detalle) {
                /** @var ReservaDetalle $detalle */
                $detalle->update([
                    'estado' => EstadoReservaDetalle::CONFIRMADO,
                    'confirmado_at' => now(),
                ]);
            }

            $nuevoEstado = $this->recalcularEstado->ejecutar($reserva);

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'motivo' => $data->observaciones ?? 'Confirmación de reserva de habitación',
                'usuario_id' => $data->usuarioId,
            ]);

            ReservaHabitacionConfirmada::dispatch($reserva);

            return $reserva->refresh();
        });
    }
}
