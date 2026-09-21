<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
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
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservaInHouseSeeder extends Seeder
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
        // Obtener habitaciones para huéspedes actuales
        $habitaciones = Habitacion::query()->where('estado', 1)->orderBy('numero')->get();
        $servicios = Servicio::query()->get();

        if ($clientes->isEmpty() || $habitaciones->isEmpty()) {
            return;
        }

        // Crear 15 huéspedes activos in-house hoy
        $totalInHouse = min(15, $habitaciones->count());

        for ($i = 0; $i < $totalInHouse; $i++) {
            $cliente = $clientes->get(($i * 3 + 1) % $clientes->count());
            $habitacion = $habitaciones->get($i);

            if (! $cliente || ! $habitacion) {
                continue;
            }

            // Entraron hace 1 a 2 días, salen en 1 a 3 días
            $diasEstanciaPasados = ($i % 2 === 0) ? 2 : 1;
            $diasRestantes = ($i % 3 === 0) ? 3 : 2;
            $totalNoches = $diasEstanciaPasados + $diasRestantes;

            $fechaEntrada = Carbon::today()->subDays($diasEstanciaPasados);
            $fechaSalida = Carbon::today()->addDays($diasRestantes);

            $tarifaNoche = (float) ($habitacion->precio_base ?? 95.00);
            if ($tarifaNoche <= 0) {
                $tarifaNoche = 95.00;
            }

            $subtotalHospedaje = round($tarifaNoche * $totalNoches, 2);

            // Consumo abierto de restaurante o room service
            $consumoRestaurante = 35.50;
            $subtotalTotal = $subtotalHospedaje + $consumoRestaurante;
            $iva = round($subtotalTotal * 0.15, 2);
            $totalEstancia = round($subtotalTotal + $iva, 2);

            $anticipoPagado = round($subtotalHospedaje * 0.50, 2);
            $saldoPendiente = round($totalEstancia - $anticipoPagado, 2);

            $codigoReserva = 'RES-INHOUSE-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            // 1. Crear Reserva en estado CHECKED_IN
            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Huésped In-House',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 8765-4321',
                    'email_cliente' => "inhouse_{$cliente->id}@hotel.com",
                    'tipo_reserva' => TipoReserva::HABITACION,
                    'habitacion_id' => $habitacion->id,
                    'fecha_check_in' => $fechaEntrada->toDateString(),
                    'fecha_check_out' => $fechaSalida->toDateString(),
                    'hora_reserva' => '15:00',
                    'adultos' => 2,
                    'ninos' => 0,
                    'solicita_cuenta' => true,
                    'limite_cuenta_solicitado' => 600.00,
                    'estado' => EstadoReserva::CHECKED_IN,
                    'subtotal' => $subtotalTotal,
                    'descuento' => 0.00,
                    'total' => $totalEstancia,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => TipoPagoReserva::ABONO_50,
                    'total_pagado' => $anticipoPagado,
                    'saldo' => $saldoPendiente,
                    'notas' => 'Huésped activo actualmente hospedado (In-House). Anticipo 50% registrado.',
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
            if ($habitacion->reservable_id !== $recurso->id || $habitacion->estado !== EstadoEspacio::Ocupado) {
                $habitacion->updateQuietly([
                    'reservable_id' => $recurso->id,
                    'estado' => EstadoEspacio::Ocupado,
                ]);
            }

            $detalleReserva = ReservaDetalle::query()->updateOrCreate(
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
                    'precio_unitario' => $tarifaNoche,
                    'subtotal' => $subtotalHospedaje,
                    'descuento' => 0.00,
                    'impuestos' => $iva,
                    'estado' => EstadoReservaDetalle::EN_USO,
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
                    'identificacion' => '001-150692-0004B',
                    'tipo_huesped' => TipoHuesped::ADULTO,
                    'email' => $reserva->email_cliente,
                    'telefono' => $reserva->telefono_cliente,
                ]
            );

            // 4. Historial
            ReservaEstadoHistorial::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'estado_nuevo' => EstadoReserva::CHECKED_IN,
                ],
                [
                    'estado_anterior' => EstadoReserva::CONFIRMADA,
                    'usuario_id' => $adminId,
                    'motivo' => 'Check-in realizado en recepción.',
                    'created_at' => $fechaEntrada->copy()->setTime(15, 10),
                ]
            );

            // 5. Estancia ACTIVA
            $estancia = Estancia::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'habitacion_id' => $habitacion->id,
                ],
                [
                    'reserva_detalle_id' => $detalleReserva->id,
                    'usuario_check_in_id' => $adminId,
                    'check_in_at' => $fechaEntrada->copy()->setTime(15, 10),
                    'check_out_at' => null,
                    'fecha_entrada_programada' => $fechaEntrada->copy()->setTime(15, 0),
                    'fecha_salida_programada' => $fechaSalida->copy()->setTime(12, 0),
                    'fecha_check_in_real' => $fechaEntrada->copy()->setTime(15, 10),
                    'cantidad_llaves' => 2,
                    'estado' => EstadoEstancia::ACTIVA,
                    'observaciones_entrada' => 'Huésped registrado correctamente. Llaves entregadas.',
                ]
            );

            // 6. Cuenta ABIERTA con cargos activos
            $numeroCuenta = 'CTA-ACTIVA-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $cuenta = Cuenta::query()->updateOrCreate(
                ['numero_cuenta' => $numeroCuenta],
                [
                    'tipo_cuenta' => TipoCuenta::ESTANCIA,
                    'estado' => EstadoCuenta::ABIERTA,
                    'cliente_id' => $cliente->id,
                    'estancia_id' => $estancia->id,
                    'reserva_id' => $reserva->id,
                    'moneda_id' => $monedaUsd->id,
                    'limite_autorizado' => 600.00,
                    'subtotal' => $subtotalTotal,
                    'descuento_total' => 0.00,
                    'impuesto_total' => $iva,
                    'cargo_servicio_total' => 0.00,
                    'propina_total' => 0.00,
                    'recargo_total' => 0.00,
                    'total' => $totalEstancia,
                    'total_pagado' => $anticipoPagado,
                    'saldo' => $saldoPendiente,
                    'abierta_at' => $fechaEntrada->copy()->setTime(15, 10),
                    'abierta_por' => $adminId,
                ]
            );

            // Cargos a la cuenta: Hospedaje + Consumo de Restaurante
            CuentaDetalle::query()->updateOrCreate(
                [
                    'cuenta_id' => $cuenta->id,
                    'concepto' => "Hospedaje {$totalNoches} noches - Hab. {$habitacion->numero}",
                ],
                [
                    'origen_type' => Estancia::class,
                    'origen_id' => $estancia->id,
                    'cantidad' => $totalNoches,
                    'precio_unitario' => $tarifaNoche,
                    'subtotal' => $subtotalHospedaje,
                    'total' => $subtotalHospedaje,
                    'estado' => 1,
                    'creador_id' => $adminId,
                ]
            );

            CuentaDetalle::query()->updateOrCreate(
                [
                    'cuenta_id' => $cuenta->id,
                    'concepto' => 'Consumo Restaurante Bugambilias (Cena Buffet)',
                ],
                [
                    'cantidad' => 1,
                    'precio_unitario' => $consumoRestaurante,
                    'subtotal' => $consumoRestaurante,
                    'total' => $consumoRestaurante,
                    'estado' => 1,
                    'creador_id' => $adminId,
                ]
            );

            // 7. Pago de anticipo registrado
            PagoCuenta::query()->updateOrCreate(
                [
                    'cuenta_id' => $cuenta->id,
                    'referencia_transaccion' => "ANTICIPO-{$codigoReserva}",
                ],
                [
                    'forma_pago' => MetodoPago::TARJETA_CREDITO,
                    'moneda_id' => $monedaUsd->id,
                    'estado' => EstadoPago::APLICADO,
                    'monto' => $anticipoPagado,
                    'propina' => 0.00,
                    'observaciones' => 'Anticipo 50% de garantía cobrado al check-in.',
                    'usuario_id' => $adminId,
                    'created_at' => $fechaEntrada->copy()->setTime(15, 15),
                ]
            );
        }
    }
}
