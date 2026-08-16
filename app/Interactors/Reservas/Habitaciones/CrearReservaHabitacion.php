<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\CrearReservaHabitacionData;
use App\BusinessLogic\Reservas\Data\RegistrarHuespedData;
use App\BusinessLogic\Reservas\RecalcularTotalesReservaHabitacion;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCreada;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CrearReservaHabitacion
{
    public function __construct(
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private RecalcularTotalesReservaHabitacion $recalcularTotales,
        private ReservaRepositorioInterface $reservas,
    ) {}

    public function ejecutar(CrearReservaHabitacionData $data, ?int $usuarioId = null): Reserva
    {
        return DB::transaction(function () use ($data): Reserva {
            // Lock room resources for update
            $this->reservas->bloquearRecursosReservables($data->recursosReservablesIds);

            // Re-validate availability
            $this->validarDisponibilidad->validarDisponibilidad(
                fechaCheckIn: $data->fechaCheckIn,
                fechaCheckOut: $data->fechaCheckOut,
                recursosReservablesIds: $data->recursosReservablesIds,
                adultos: $data->adultos,
                ninos: $data->ninos,
            );

            $codigoReserva = 'RES-HAB-'.strtoupper(Str::random(8));
            $holdExpiresAt = $data->holdExpiresMinutes !== null
                ? now()->addMinutes($data->holdExpiresMinutes)
                : null;

            $reserva = $this->reservas->crear([
                'codigo_reserva' => $codigoReserva,
                'cliente_id' => $data->clienteId,
                'nombre_cliente' => $data->nombreCliente,
                'telefono_cliente' => $data->telefonoCliente,
                'email_cliente' => $data->emailCliente,
                'tipo_reserva' => TipoReserva::HABITACION,
                'fecha_check_in' => $data->fechaCheckIn,
                'fecha_check_out' => $data->fechaCheckOut,
                'adultos' => $data->adultos,
                'ninos' => $data->ninos,
                'moneda_id' => $data->monedaId,
                'tipo_pago' => $data->tipoPago,
                'solicita_cuenta' => $data->solicitaCuenta,
                'limite_cuenta_solicitado' => $data->limiteCuentaSolicitado,
                'estado' => EstadoReserva::PENDIENTE,
                'notas' => $data->notas,
                'subtotal' => 0,
                'descuento' => 0,
                'total' => 0,
                'total_pagado' => 0,
                'saldo' => 0,
            ]);

            foreach ($data->recursosReservablesIds as $index => $recursoId) {
                $recurso = $this->reservas->obtenerRecursoConLock($recursoId);
                $precioNoche = (float) ($recurso->habitacion?->precios()->first()->precio ?? 0.0);
                $dias = max(1, (int) $data->fechaCheckIn->diffInDays($data->fechaCheckOut));
                $subtotal = $precioNoche * $dias;

                $detalle = $this->reservas->crearDetalle($reserva, $recurso, [
                    'estado' => EstadoReservaDetalle::PENDIENTE,
                    'fecha_inicio' => $data->fechaCheckIn,
                    'fecha_fin' => $data->fechaCheckOut,
                    'cantidad' => 1,
                    'adultos' => $data->adultos,
                    'ninos' => $data->ninos,
                    'precio_unitario' => $precioNoche,
                    'subtotal' => $subtotal,
                    'hold_expires_at' => $holdExpiresAt,
                ]);

                $huespedesInput = $data->huespedesPorHabitacion[$index] ?? $data->huespedesPorHabitacion[$recursoId] ?? [];
                foreach ($huespedesInput as $huespedData) {
                    /** @var RegistrarHuespedData $huespedData */
                    $this->reservas->crearHuesped($detalle, [
                        'nombre' => $huespedData->nombre,
                        'apellido' => $huespedData->apellido,
                        'numero_documento' => $huespedData->numeroDocumento,
                        'email' => $huespedData->email,
                        'telefono' => $huespedData->telefono,
                        'tipo_huesped' => $huespedData->tipoHuesped,
                        'es_titular' => $huespedData->esTitular,
                    ]);
                }
            }

            $this->recalcularTotales->ejecutar($reserva);

            ReservaCreada::dispatch($reserva);

            return $reserva->refresh();
        });
    }
}
