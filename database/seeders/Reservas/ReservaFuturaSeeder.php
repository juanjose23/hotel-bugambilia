<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservaFuturaSeeder extends Seeder
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
        $habitaciones = Habitacion::query()->where('estado', 1)->orderByDesc('id')->get();

        if ($clientes->isEmpty() || $habitaciones->isEmpty()) {
            return;
        }

        // Crear 40 reservas proyectadas para los próximos 1 a 6 meses
        for ($i = 1; $i <= 40; $i++) {
            $cliente = $clientes->get(($i * 4 + 3) % $clientes->count());
            $habitacion = $habitaciones->get($i % $habitaciones->count());

            if (! $cliente || ! $habitacion) {
                continue;
            }

            $diasEnElFuturo = ($i * 12) + rand(2, 6);
            $noches = rand(2, 5);

            $fechaEntrada = Carbon::today()->addDays($diasEnElFuturo);
            $fechaSalida = (clone $fechaEntrada)->addDays($noches);

            $esConfirmada = ($i % 3 !== 0); // 2 de cada 3 son confirmadas con depósito
            $tarifaNoche = (float) ($habitacion->precio_base ?? 90.00);
            if ($tarifaNoche <= 0) {
                $tarifaNoche = 90.00;
            }

            $subtotal = round($tarifaNoche * $noches, 2);
            $iva = round($subtotal * 0.15, 2);
            $total = round($subtotal + $iva, 2);

            $totalPagado = $esConfirmada ? round($total * 0.50, 2) : 0.00;
            $saldo = round($total - $totalPagado, 2);

            $codigoReserva = 'RES-FUT-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            // 1. Crear Reserva
            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Cliente Reserva Futura',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 8555-1234',
                    'email_cliente' => "futura_{$cliente->id}@hotel.com",
                    'tipo_reserva' => TipoReserva::HABITACION,
                    'habitacion_id' => $habitacion->id,
                    'fecha_check_in' => $fechaEntrada->toDateString(),
                    'fecha_check_out' => $fechaSalida->toDateString(),
                    'hora_reserva' => '14:00',
                    'adultos' => 2,
                    'ninos' => ($i % 2 === 0) ? 1 : 0,
                    'solicita_cuenta' => true,
                    'limite_cuenta_solicitado' => 500.00,
                    'estado' => $esConfirmada ? EstadoReserva::CONFIRMADA : EstadoReserva::PENDIENTE,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'total' => $total,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => $esConfirmada ? TipoPagoReserva::ABONO_50 : TipoPagoReserva::SIN_PAGO,
                    'total_pagado' => $totalPagado,
                    'saldo' => $saldo,
                    'notas' => $esConfirmada
                        ? 'Reserva futura confirmada con depósito de garantía.'
                        : 'Reserva futura pendiente de confirmación por el huésped.',
                ]
            );

            // 2. Recurso Reservable y Detalle
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

            $detalleReserva = ReservaDetalle::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'reservable_id' => $recurso->id,
                ],
                [
                    'fecha_inicio' => $fechaEntrada->toDateString().' 14:00:00',
                    'fecha_fin' => $fechaSalida->toDateString().' 11:00:00',
                    'cantidad' => 1,
                    'adultos' => 2,
                    'ninos' => ($i % 2 === 0) ? 1 : 0,
                    'precio_unitario' => $tarifaNoche,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'impuestos' => $iva,
                    'estado' => $esConfirmada ? EstadoReservaDetalle::CONFIRMADO : EstadoReservaDetalle::PENDIENTE,
                ]
            );

            // 3. Huésped titular
            ReservaHuesped::query()->updateOrCreate(
                [
                    'reserva_detalle_id' => $detalleReserva->id,
                    'es_titular' => true,
                ],
                [
                    'nombre' => $reserva->nombre_cliente,
                    'identificacion' => '001-200495-0008K',
                    'tipo_huesped' => TipoHuesped::ADULTO,
                    'email' => $reserva->email_cliente,
                    'telefono' => $reserva->telefono_cliente,
                ]
            );

            // 4. Historial
            ReservaEstadoHistorial::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'estado_nuevo' => $esConfirmada ? EstadoReserva::CONFIRMADA : EstadoReserva::PENDIENTE,
                ],
                [
                    'estado_anterior' => EstadoReserva::PENDIENTE,
                    'usuario_id' => $adminId,
                    'motivo' => $esConfirmada ? 'Depósito del 50% verificado y aplicado.' : 'Solicitud de reserva ingresada vía web.',
                    'created_at' => now(),
                ]
            );

            // 5. Cuenta Solicitada y Pago si está confirmada
            if ($esConfirmada) {
                $cuenta = Cuenta::query()->updateOrCreate(
                    ['numero_cuenta' => "CTA-FUT-{$codigoReserva}"],
                    [
                        'tipo_cuenta' => TipoCuenta::ESTANCIA,
                        'estado' => EstadoCuenta::SOLICITADA,
                        'cliente_id' => $cliente->id,
                        'reserva_id' => $reserva->id,
                        'moneda_id' => $monedaUsd->id,
                        'limite_autorizado' => 500.00,
                        'subtotal' => $subtotal,
                        'descuento_total' => 0.00,
                        'impuesto_total' => $iva,
                        'cargo_servicio_total' => 0.00,
                        'propina_total' => 0.00,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'total_pagado' => $totalPagado,
                        'saldo' => $saldo,
                        'abierta_at' => now(),
                        'abierta_por' => $adminId,
                    ]
                );

                PagoCuenta::query()->updateOrCreate(
                    [
                        'cuenta_id' => $cuenta->id,
                        'referencia_transaccion' => "ANTICIPO-FUT-{$codigoReserva}",
                    ],
                    [
                        'forma_pago' => MetodoPago::TARJETA_CREDITO,
                        'moneda_id' => $monedaUsd->id,
                        'estado' => EstadoPago::APLICADO,
                        'monto' => $totalPagado,
                        'propina' => 0.00,
                        'observaciones' => 'Anticipo 50% de garantía cobrado por pasarela en línea.',
                        'usuario_id' => $adminId,
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
