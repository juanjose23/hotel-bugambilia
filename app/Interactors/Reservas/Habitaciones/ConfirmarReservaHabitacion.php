<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\ConfirmarReservaHabitacionData;
use App\BusinessLogic\Reservas\RecalcularEstadoReservaHabitacion;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\BusinessLogic\Reservas\ValidarPoliticaPagoReserva;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\ReservaConfirmada;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarReservaHabitacion
{
    /**
     * Todos los dependencies se inyectan explícitamente (sin valores por defecto `new`)
     * para facilitar testing con mocks y respetar el principio de inversión de dependencias.
     */
    public function __construct(
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private RecalcularEstadoReservaHabitacion $recalcularEstado,
        private ReservaRepositorioInterface $reservas,
        private ValidarPoliticaPagoReserva $validarPoliticaPago,
    ) {}

    public function ejecutar(ConfirmarReservaHabitacionData $data): Reserva
    {
        return DB::transaction(function () use ($data): Reserva {
            $reserva = $this->reservas->obtenerPorIdConLock($data->reservaId);

            if ($reserva->estado !== EstadoReserva::PENDIENTE) {
                throw new DomainException("La reserva #{$reserva->codigo_reserva} no se encuentra en estado pendiente.");
            }

            // Validar política de pago (e.g. 50% abono / 100% pago completo)
            $this->validarPoliticaPago->validarMontoParaConfirmacion($reserva);

            $detallesPrincipales = $this->reservas->detallesPrincipalesDe($reserva);
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
                $this->reservas->actualizarDetalle($detalle, [
                    'estado' => EstadoReservaDetalle::CONFIRMADO,
                    'confirmado_at' => now(),
                ]);
            }

            $nuevoEstado = $this->recalcularEstado->ejecutar($reserva);

            $this->reservas->registrarHistorial(
                $reserva,
                $estadoAnterior,
                $nuevoEstado,
                $data->observaciones ?? 'Confirmación de reserva de habitación',
                $data->usuarioId,
            );

            ReservaConfirmada::dispatch($reserva);

            return $reserva->refresh();
        });
    }
}
