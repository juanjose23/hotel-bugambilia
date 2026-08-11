<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\CheckOut\ValidarRequisitosCheckOut;
use App\BusinessLogic\Reservas\Data\RealizarCheckOutData;
use App\BusinessLogic\Reservas\RecalcularEstadoReservaHabitacion;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\CheckOutHabitacionRealizado;
use App\Events\Reservas\HabitacionPendienteDeLimpieza;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class RealizarCheckOutHabitacion
{
    public function __construct(
        private RecalcularEstadoReservaHabitacion $recalcularEstado,
        private ValidarRequisitosCheckOut $validarRequisitos = new ValidarRequisitosCheckOut,
    ) {}

    public function ejecutar(RealizarCheckOutData $data): Estancia
    {
        return DB::transaction(function () use ($data): Estancia {
            $estancia = Estancia::query()
                ->where('id', $data->estanciaId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($estancia->estado !== EstadoEstancia::ACTIVA && $estancia->estado !== EstadoEstancia::EXTENDIDA) {
                throw new DomainException("La estancia #{$estancia->id} no se encuentra activa para Check-Out.");
            }

            $detalle = $estancia->reservaDetalle()->lockForUpdate()->first();
            if (! $detalle) {
                $detalle = ReservaDetalle::query()->where('reserva_id', $estancia->reserva_id)->firstOrFail();
            }

            $reserva = $estancia->reserva()->lockForUpdate()->firstOrFail();

            /** @var Habitacion $habitacion */
            $habitacion = $estancia->habitacion()->lockForUpdate()->firstOrFail();

            $this->validarRequisitos->validar($estancia, [
                'credito_autorizado' => $data->autorizarSaldoPendiente,
                'llaves_devueltas' => $data->llavesDevueltas,
                'autorizar_llaves_pendientes' => $data->autorizarLlavesPendientes,
            ]);

            $cuenta = Cuenta::query()
                ->where('estancia_id', $estancia->id)
                ->orWhere('reserva_id', $reserva->id)
                ->lockForUpdate()
                ->first();

            if ($cuenta && (float) $cuenta->saldo > 0.0 && ! $data->autorizarSaldoPendiente) {
                throw new DomainException("No se puede realizar Check-Out: La cuenta #{$cuenta->numero_cuenta} tiene un saldo pendiente de ${$cuenta->saldo} sin autorizar.");
            }

            if ($cuenta) {
                $cuenta->update([
                    'estado' => EstadoCuenta::CERRADA,
                    'cerrada_at' => now(),
                    'cerrada_por' => $data->usuarioId,
                ]);
            }

            $estancia->update([
                'estado' => EstadoEstancia::FINALIZADA,
                'check_out_at' => now(),
                'fecha_check_out_real' => now(),
                'usuario_check_out_id' => $data->usuarioId,
                'observaciones_salida' => $data->observaciones,
            ]);

            $detalle->update([
                'estado' => EstadoReservaDetalle::COMPLETADO,
            ]);

            $habitacion->update([
                'estado' => EstadoEspacio::Sucio,
            ]);

            $estadoAnterior = $reserva->estado;
            $nuevoEstado = $this->recalcularEstado->ejecutar($reserva);

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'motivo' => "Check-out realizado para la habitación {$habitacion->numero} (Estancia #{$estancia->id})",
                'usuario_id' => $data->usuarioId,
            ]);

            CheckOutHabitacionRealizado::dispatch($estancia, $detalle);
            HabitacionPendienteDeLimpieza::dispatch($habitacion, "Habitacion {$habitacion->numero} dejada libre por Check-Out");

            return $estancia->refresh();
        });
    }
}
