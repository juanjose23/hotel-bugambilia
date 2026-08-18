<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\CheckIn\ValidarRequisitosCheckIn;
use App\BusinessLogic\Reservas\Data\RealizarCheckInData;
use App\BusinessLogic\Reservas\RecalcularEstadoReservaHabitacion;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Events\Reservas\CheckInRegistrado;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class RealizarCheckInHabitacion
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
        private ValidarRequisitosCheckIn $validarRequisitos,
    ) {}

    public function ejecutar(RealizarCheckInData $data): Estancia
    {
        return DB::transaction(function () use ($data): Estancia {
            $detalle = $this->reservas->obtenerDetalleConLock($data->reservaDetalleId);

            $reserva = $this->reservas->obtenerReservaDeDetalleConLock($detalle);

            if (! in_array($detalle->estado, [EstadoReservaDetalle::CONFIRMADO, EstadoReservaDetalle::PENDIENTE], true)) {
                throw new DomainException("El detalle de reserva #{$detalle->id} no está en estado confirmado o pendiente para Check-In.");
            }

            $recurso = $detalle->reservable;
            if ($recurso === null) {
                throw new DomainException("El detalle de reserva #{$detalle->id} no tiene un recurso reservable asociado.");
            }

            $habitacion = $this->habitaciones->buscarPorRecursoReservableIdConLock((int) $recurso->id);

            if (! $habitacion) {
                throw new DomainException("No se encontró una habitación física asociada al recurso reservable #{$recurso->id}.");
            }

            if (! in_array($habitacion->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                throw new DomainException("La habitación {$habitacion->nombre} (N° {$habitacion->numero}) no está operacionalmente disponible para Check-In. Estado actual: {$habitacion->estado->getLabel()}.");
            }

            if ($reserva->habitacion_id === null) {
                $this->reservas->actualizar($reserva, ['habitacion_id' => $habitacion->id]);
            }

            if ($this->reservas->existeEstanciaActivaParaDetalle((int) $detalle->id)) {
                throw new DomainException("Ya existe una estancia activa para el detalle de reserva #{$detalle->id}.");
            }

            // Confirm/update guests if provided
            if (! empty($data->huespedes)) {
                foreach ($data->huespedes as $huespedData) {
                    $nombreCompleto = trim($huespedData->nombre.' '.($huespedData->apellido ?? ''));
                    $this->reservas->crearHuesped($detalle, [
                        'nombre' => $nombreCompleto !== '' ? $nombreCompleto : $huespedData->nombre,
                        'identificacion' => $huespedData->numeroDocumento,
                        'email' => $huespedData->email,
                        'telefono' => $huespedData->telefono,
                        'tipo_huesped' => $huespedData->tipoHuesped,
                        'es_titular' => $huespedData->esTitular,
                    ]);
                }
                $reserva->unsetRelation('huespedes');
                $reserva->unsetRelation('detalles');
            }

            $this->validarRequisitos->validar($reserva, $detalle);

            $estancia = $this->reservas->crearEstancia([
                'reserva_id' => $reserva->id,
                'reserva_detalle_id' => $detalle->id,
                'habitacion_id' => $habitacion->id,
                'fecha_entrada_programada' => $detalle->fecha_inicio,
                'fecha_salida_programada' => $detalle->fecha_fin,
                'fecha_check_in_real' => now(),
                'check_in_at' => now(),
                'cantidad_llaves' => $data->cantidadLlaves,
                'estado' => EstadoEstancia::ACTIVA,
                'usuario_check_in_id' => $data->usuarioId,
                'observaciones_entrada' => $data->observaciones,
            ]);

            $this->reservas->actualizarDetalle($detalle, ['estado' => EstadoReservaDetalle::EN_USO]);
            $this->habitaciones->actualizarEstado($habitacion, EstadoEspacio::Ocupado);

            // Create account if not present or activate requested account
            $this->cuentas->crear([
                'numero_cuenta' => 'CTA-EST-'.strtoupper(Str::random(8)),
                'tipo_cuenta' => TipoCuenta::ESTANCIA,
                'estado' => EstadoCuenta::ABIERTA,
                'cliente_id' => $reserva->cliente_id,
                'reserva_id' => $reserva->id,
                'estancia_id' => $estancia->id,
                'moneda_id' => $reserva->moneda_id,
                'limite_autorizado' => $data->limiteCuenta ?? $reserva->limite_cuenta_solicitado,
                'subtotal' => $detalle->subtotal,
                'descuento_total' => $detalle->descuento,
                'impuesto_total' => $detalle->impuestos,
                'cargo_servicio_total' => 0,
                'propina_total' => 0,
                'recargo_total' => 0,
                'total' => $detalle->subtotal,
                'total_pagado' => 0,
                'saldo' => $detalle->subtotal,
                'abierta_at' => now(),
                'abierta_por' => $data->usuarioId,
            ]);

            $estadoAnterior = $reserva->estado;
            $nuevoEstado = $this->recalcularEstado->ejecutar($reserva);

            $this->reservas->registrarHistorial(
                $reserva,
                $estadoAnterior,
                $nuevoEstado,
                "Check-in realizado para la habitación {$habitacion->numero} (Detalle #{$detalle->id})",
                $data->usuarioId,
            );

            CheckInRegistrado::dispatch($estancia);

            return $estancia->refresh();
        });
    }
}
