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
use App\Events\Reservas\CheckInHabitacionRealizado;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class RealizarCheckInHabitacion
{
    public function __construct(
        private RecalcularEstadoReservaHabitacion $recalcularEstado,
        private ValidarRequisitosCheckIn $validarRequisitos = new ValidarRequisitosCheckIn,
    ) {}

    public function ejecutar(RealizarCheckInData $data): Estancia
    {
        return DB::transaction(function () use ($data): Estancia {
            $detalle = ReservaDetalle::query()
                ->where('id', $data->reservaDetalleId)
                ->lockForUpdate()
                ->firstOrFail();

            $reserva = $detalle->reserva()->lockForUpdate()->firstOrFail();

            if (! in_array($detalle->estado, [EstadoReservaDetalle::CONFIRMADO, EstadoReservaDetalle::PENDIENTE], true)) {
                throw new DomainException("El detalle de reserva #{$detalle->id} no está en estado confirmado o pendiente para Check-In.");
            }

            $recurso = $detalle->reservable;
            if ($recurso === null) {
                throw new DomainException("El detalle de reserva #{$detalle->id} no tiene un recurso reservable asociado.");
            }

            $habitacion = Habitacion::query()
                ->where('reservable_id', $recurso->id)
                ->lockForUpdate()
                ->first();

            if (! $habitacion) {
                throw new DomainException("No se encontró una habitación física asociada al recurso reservable #{$recurso->id}.");
            }

            if (! in_array($habitacion->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                throw new DomainException("La habitación {$habitacion->nombre} (N° {$habitacion->numero}) no está operacionalmente disponible para Check-In. Estado actual: {$habitacion->estado->getLabel()}.");
            }

            if ($reserva->habitacion_id === null) {
                $reserva->update(['habitacion_id' => $habitacion->id]);
            }

            // Check if estancia active already exists
            $estanciaExistente = Estancia::query()
                ->where('reserva_detalle_id', $detalle->id)
                ->where('estado', EstadoEstancia::ACTIVA)
                ->exists();

            if ($estanciaExistente) {
                throw new DomainException("Ya existe una estancia activa para el detalle de reserva #{$detalle->id}.");
            }

            // Confirm/update guests if provided
            if (! empty($data->huespedes)) {
                foreach ($data->huespedes as $huespedData) {
                    $nombreCompleto = trim($huespedData->nombre.' '.($huespedData->apellido ?? ''));
                    ReservaHuesped::query()->create([
                        'reserva_detalle_id' => $detalle->id,
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

            $estancia = Estancia::query()->create([
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

            $detalle->update(['estado' => EstadoReservaDetalle::EN_USO]);
            $habitacion->update(['estado' => EstadoEspacio::Ocupado]);

            // Create account if not present or activate requested account
            Cuenta::query()->create([
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

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'motivo' => "Check-in realizado para la habitación {$habitacion->numero} (Detalle #{$detalle->id})",
                'usuario_id' => $data->usuarioId,
            ]);

            CheckInHabitacionRealizado::dispatch($detalle, $estancia);

            return $estancia->refresh();
        });
    }
}
