<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaBitacora;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservaExcepcionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@hotel.com')->first() ?? User::query()->first();
        $adminId = $admin?->id;

        $monedaUsd = Moneda::query()->where('codigo', 'USD')->first()
            ?? Moneda::query()->where('codigo', 'NIO')->first()
            ?? Moneda::query()->first();

        if (! $monedaUsd) {
            return;
        }

        $clientes = Cliente::query()->with('persona')->get();
        $habitaciones = Habitacion::query()->where('estado', 1)->get();

        if ($clientes->isEmpty() || $habitaciones->isEmpty()) {
            return;
        }

        // 1. CASOS DE CANCELACIÓN (2 casos)
        for ($i = 1; $i <= 2; $i++) {
            $cliente = $clientes->get(($i + 5) % $clientes->count());
            $habitacion = $habitaciones->get(($i + 7) % $habitaciones->count());

            if (! $cliente || ! $habitacion) {
                continue;
            }

            $fechaEntrada = Carbon::today()->subDays(15 * $i);
            $fechaSalida = (clone $fechaEntrada)->addDays(3);
            $codigoReserva = "RES-CANC-00{$i}";

            $tarifa = (float) ($habitacion->precio_base ?? 80.00);
            $subtotal = round($tarifa * 3, 2);
            $total = round($subtotal * 1.15, 2);

            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Cliente Cancelación',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 8111-2222',
                    'email_cliente' => "canc_{$cliente->id}@hotel.com",
                    'tipo_reserva' => TipoReserva::HABITACION,
                    'habitacion_id' => $habitacion->id,
                    'fecha_check_in' => $fechaEntrada->toDateString(),
                    'fecha_check_out' => $fechaSalida->toDateString(),
                    'adultos' => 2,
                    'ninos' => 0,
                    'solicita_cuenta' => false,
                    'estado' => EstadoReserva::CANCELADA,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'total' => $total,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => TipoPagoReserva::SIN_PAGO,
                    'total_pagado' => 0.00,
                    'saldo' => 0.00,
                    'notas' => 'Reserva cancelada por el cliente con más de 72 horas de anticipación conforme a política.',
                ]
            );

            // Recurso Reservable y Detalle
            $recurso = RecursoReservable::firstOrCreate(
                ['nombre' => "Habitación {$habitacion->numero}", 'tipo' => TipoRecursoReservable::HABITACION],
                [
                    'capacidad' => 2,
                    'control_disponibilidad' => ControlDisponibilidad::FECHAS,
                    'duracion_minutos' => 1440,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                ]
            );
            if (! $habitacion->reservable_id) {
                $habitacion->updateQuietly(['reservable_id' => $recurso->id]);
            }

            $detalle = ReservaDetalle::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'reservable_id' => $recurso->id,
                ],
                [
                    'fecha_inicio' => $fechaEntrada->toDateString().' 15:00:00',
                    'fecha_fin' => $fechaSalida->toDateString().' 12:00:00',
                    'cantidad' => 1,
                    'adultos' => 2,
                    'ninos' => 0,
                    'precio_unitario' => $tarifa,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'impuestos' => round($subtotal * 0.15, 2),
                    'estado' => EstadoReservaDetalle::CANCELADO,
                ]
            );

            ReservaHuesped::query()->updateOrCreate(
                [
                    'reserva_detalle_id' => $detalle->id,
                    'es_titular' => true,
                ],
                [
                    'nombre' => $reserva->nombre_cliente,
                    'identificacion' => '001-010190-0001A',
                    'tipo_huesped' => TipoHuesped::ADULTO,
                    'email' => $reserva->email_cliente,
                    'telefono' => $reserva->telefono_cliente,
                ]
            );

            ReservaEstadoHistorial::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'estado_nuevo' => EstadoReserva::CANCELADA,
                ],
                [
                    'estado_anterior' => EstadoReserva::CONFIRMADA,
                    'usuario_id' => $adminId,
                    'motivo' => 'Cancelación voluntaria por motivos de itinerario de viaje.',
                    'created_at' => $fechaEntrada->copy()->subDays(5),
                ]
            );

            ReservaBitacora::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'tipo' => 'CANCELACION_PROCESADA',
                ],
                [
                    'datos' => [
                        'descripcion' => 'Se procesó la cancelación sin penalidad según política flexible.',
                        'usuario_id' => $adminId,
                        'ip' => '127.0.0.1',
                    ],
                ]
            );
        }

        // 2. CASOS DE NO-SHOW (2 casos)
        for ($i = 1; $i <= 2; $i++) {
            $cliente = $clientes->get(($i + 9) % $clientes->count());
            $habitacion = $habitaciones->get(($i + 12) % $habitaciones->count());

            if (! $cliente || ! $habitacion) {
                continue;
            }

            $fechaEntrada = Carbon::today()->subDays(20 * $i);
            $fechaSalida = (clone $fechaEntrada)->addDays(2);
            $codigoReserva = "RES-NOSHOW-00{$i}";

            $tarifa = (float) ($habitacion->precio_base ?? 85.00);
            $subtotal = round($tarifa * 2, 2);
            $total = round($subtotal * 1.15, 2);
            $anticipoRetenido = round($total * 0.50, 2);

            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Cliente No-Show',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 8333-4444',
                    'email_cliente' => "noshow_{$cliente->id}@hotel.com",
                    'tipo_reserva' => TipoReserva::HABITACION,
                    'habitacion_id' => $habitacion->id,
                    'fecha_check_in' => $fechaEntrada->toDateString(),
                    'fecha_check_out' => $fechaSalida->toDateString(),
                    'adultos' => 1,
                    'ninos' => 0,
                    'solicita_cuenta' => false,
                    'estado' => EstadoReserva::NO_SHOW,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'total' => $total,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => TipoPagoReserva::SIN_PAGO,
                    'total_pagado' => $anticipoRetenido,
                    'saldo' => 0.00,
                    'notas' => 'Huésped no se presentó en la fecha convenida. Depósito de 1 noche retenido según política de No-Show.',
                ]
            );

            // Recurso Reservable y Detalle
            $recurso = RecursoReservable::firstOrCreate(
                ['nombre' => "Habitación {$habitacion->numero}", 'tipo' => TipoRecursoReservable::HABITACION],
                [
                    'capacidad' => 2,
                    'control_disponibilidad' => ControlDisponibilidad::FECHAS,
                    'duracion_minutos' => 1440,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                ]
            );
            if (! $habitacion->reservable_id) {
                $habitacion->updateQuietly(['reservable_id' => $recurso->id]);
            }

            $detalle = ReservaDetalle::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'reservable_id' => $recurso->id,
                ],
                [
                    'fecha_inicio' => $fechaEntrada->toDateString().' 15:00:00',
                    'fecha_fin' => $fechaSalida->toDateString().' 12:00:00',
                    'cantidad' => 1,
                    'adultos' => 1,
                    'ninos' => 0,
                    'precio_unitario' => $tarifa,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'impuestos' => round($subtotal * 0.15, 2),
                    'estado' => EstadoReservaDetalle::CANCELADO,
                ]
            );

            ReservaHuesped::query()->updateOrCreate(
                [
                    'reserva_detalle_id' => $detalle->id,
                    'es_titular' => true,
                ],
                [
                    'nombre' => $reserva->nombre_cliente,
                    'identificacion' => '001-010190-0001A',
                    'tipo_huesped' => TipoHuesped::ADULTO,
                    'email' => $reserva->email_cliente,
                    'telefono' => $reserva->telefono_cliente,
                ]
            );

            ReservaEstadoHistorial::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'estado_nuevo' => EstadoReserva::NO_SHOW,
                ],
                [
                    'estado_anterior' => EstadoReserva::CONFIRMADA,
                    'usuario_id' => $adminId,
                    'motivo' => 'Cierre nocturno automático: Huésped no se presentó.',
                    'created_at' => $fechaEntrada->copy()->setTime(23, 59),
                ]
            );

            ReservaBitacora::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'tipo' => 'NO_SHOW_DECLARADO',
                ],
                [
                    'datos' => [
                        'descripcion' => "No-show declarado tras finalizar el día {$fechaEntrada->toDateString()}. Se retiene el anticipo.",
                        'usuario_id' => $adminId,
                        'ip' => '127.0.0.1',
                    ],
                ]
            );
        }
    }
}
