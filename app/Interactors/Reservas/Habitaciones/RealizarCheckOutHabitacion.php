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
use App\Events\Reservas\CheckOutRegistrado;
use App\Events\Reservas\HabitacionPendienteDeLimpieza;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class RealizarCheckOutHabitacion
{
    /**
     * Todos los dependencies se inyectan explícitamente (sin valores por defecto `new`)
     * para facilitar testing con mocks y respetar el principio de inversión de dependencias.
     */
    public function __construct(
        private RecalcularEstadoReservaHabitacion $recalcularEstado,
        private ReservaRepositorioInterface $reservas,
        private HabitacionRepositorioInterface $habitaciones,
        private CuentaRepositorioInterface $cuentas,
        private ValidarRequisitosCheckOut $validarRequisitos,
    ) {}

    public function ejecutar(RealizarCheckOutData $data): Estancia
    {
        return DB::transaction(function () use ($data): Estancia {
            $estancia = $this->reservas->estanciaConLock($data->estanciaId);

            if ($estancia->estado !== EstadoEstancia::ACTIVA && $estancia->estado !== EstadoEstancia::EXTENDIDA) {
                throw new DomainException("La estancia #{$estancia->id} no se encuentra activa para Check-Out.");
            }

            $detalle = $this->reservas->obtenerDetalleDeEstanciaConLock($estancia);
            if (! $detalle) {
                $detalle = $this->reservas->obtenerDetalleDeReservaConLock((int) $estancia->reserva_id);
            }

            $reserva = $this->reservas->obtenerReservaDeEstanciaConLock($estancia);

            $habitacion = $this->habitaciones->buscarPorIdConLock((int) $estancia->habitacion_id);

            $this->validarRequisitos->validar($estancia, [
                'credito_autorizado' => $data->autorizarSaldoPendiente,
                'llaves_devueltas' => $data->llavesDevueltas,
                'autorizar_llaves_pendientes' => $data->autorizarLlavesPendientes,
            ]);

            $cuenta = $this->cuentas->cuentaDeEstanciaOReservaConLock((int) $estancia->id, (int) $reserva->id);

            if ($cuenta && (float) $cuenta->saldo > 0.0 && ! $data->autorizarSaldoPendiente) {
                throw new DomainException("No se puede realizar Check-Out: La cuenta #{$cuenta->numero_cuenta} tiene un saldo pendiente de {$cuenta->saldo} sin autorizar.");
            }

            if ($cuenta) {
                $this->cuentas->actualizar($cuenta, [
                    'estado' => EstadoCuenta::CERRADA,
                    'cerrada_at' => now(),
                    'cerrada_por' => $data->usuarioId,
                ]);
            }

            $this->reservas->actualizarEstancia($estancia, [
                'estado' => EstadoEstancia::FINALIZADA,
                'check_out_at' => now(),
                'fecha_check_out_real' => now(),
                'usuario_check_out_id' => $data->usuarioId,
                'observaciones_salida' => $data->observaciones,
            ]);

            $this->reservas->actualizarDetalle($detalle, [
                'estado' => EstadoReservaDetalle::COMPLETADO,
            ]);

            $this->habitaciones->actualizarEstado($habitacion, EstadoEspacio::Sucio);

            $estadoAnterior = $reserva->estado;
            $nuevoEstado = $this->recalcularEstado->ejecutar($reserva);

            $this->reservas->registrarHistorial(
                $reserva,
                $estadoAnterior,
                $nuevoEstado,
                "Check-out realizado para la habitación {$habitacion->numero} (Estancia #{$estancia->id})",
                $data->usuarioId,
            );

            CheckOutRegistrado::dispatch($estancia);
            HabitacionPendienteDeLimpieza::dispatch($habitacion, "Habitacion {$habitacion->numero} dejada libre por Check-Out");

            return $estancia->refresh();
        });
    }
}
