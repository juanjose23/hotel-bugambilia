<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\EstadoFolioFactura;
use App\Enums\Facturacion\TipoFactura;
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
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class ReservaServicioPaqueteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@hotel.com')->first() ?? User::query()->first();
        $adminId = $admin?->id;

        $monedaUsd = Moneda::query()->where('codigo', 'USD')->first()
            ?? Moneda::query()->where('codigo', 'NIO')->first()
            ?? Moneda::query()->first();

        $monedaNio = Moneda::query()->where('codigo', 'NIO')->first() ?? $monedaUsd;

        if (! $monedaUsd || ! $monedaNio) {
            return;
        }

        $clientes = Cliente::query()->with('persona')->get();
        $servicios = Servicio::query()->get();
        $habitaciones = Habitacion::query()->where('estado', 1)->get();
        $paquetes = Promocion::query()->get();

        if ($clientes->isEmpty() || $servicios->isEmpty()) {
            return;
        }

        $serieB = FacturaSerie::query()->where('codigo', 'B')->first() ?? FacturaSerie::query()->first();
        $authB = $serieB ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $serieB->id)->first() : null;
        $tasaCambio = 36.50;

        // Sembrar 15 reservas de paquetes híbridos y servicios individuales
        for ($i = 1; $i <= 15; $i++) {
            $cliente = $clientes->get(($i * 5 + 4) % $clientes->count());
            $esPaquete = ($i % 2 === 0);
            $servicio = $servicios->get($i % $servicios->count());
            $habitacion = $habitaciones->isNotEmpty() ? $habitaciones->get($i % $habitaciones->count()) : null;

            if (! $cliente || ! $servicio) {
                continue;
            }

            $esPasada = ($i <= 10);
            $diasOffset = $esPasada ? ($i * 15) : (-1 * ($i - 10) * 10);
            $fechaReserva = Carbon::today()->subDays($diasOffset)->setTime(10, 0);
            $estado = $esPasada ? EstadoReserva::CHECKED_OUT : EstadoReserva::CONFIRMADA;

            $tipoReserva = $esPaquete ? TipoReserva::PAQUETE : TipoReserva::SERVICIO;
            $precioBase = $esPaquete ? 280.00 : (float) ($servicio->precio_base ?? 45.00);
            if ($precioBase <= 0) {
                $precioBase = 45.00;
            }

            $subtotal = $precioBase;
            $iva = round($subtotal * 0.15, 2);
            $total = round($subtotal + $iva, 2);

            $totalPagado = $esPasada ? $total : round($total * 0.50, 2);
            $saldo = round($total - $totalPagado, 2);

            $codigoReserva = 'RES-PAQ-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            // 1. Crear Reserva
            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Cliente Spa y Paquetes',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 8555-4321',
                    'email_cliente' => "paquete_{$cliente->id}@hotel.com",
                    'tipo_reserva' => $tipoReserva,
                    'servicio_id' => $servicio->id,
                    'habitacion_id' => $esPaquete && $habitacion ? $habitacion->id : null,
                    'fecha_check_in' => $fechaReserva->toDateString(),
                    'fecha_check_out' => $esPaquete ? $fechaReserva->copy()->addDays(2)->toDateString() : $fechaReserva->toDateString(),
                    'hora_reserva' => '10:00',
                    'adultos' => 2,
                    'ninos' => 0,
                    'solicita_cuenta' => true,
                    'limite_cuenta_solicitado' => 500.00,
                    'estado' => $estado,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'total' => $total,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => $esPasada ? TipoPagoReserva::PAGO_COMPLETO : TipoPagoReserva::ABONO_50,
                    'total_pagado' => $totalPagado,
                    'saldo' => $saldo,
                    'notas' => $esPaquete
                        ? 'Paquete turístico híbrido: Hospedaje 2 noches + Sesión Spa Relax + Cena Romántica.'
                        : "Servicio de bienestar: {$servicio->nombre}.",
                ]
            );

            // 2. Recurso y Detalle
            $recurso = RecursoReservable::firstOrCreate(
                ['nombre' => $servicio->nombre, 'tipo' => TipoRecursoReservable::SERVICIO],
                [
                    'capacidad' => 2,
                    'control_disponibilidad' => ControlDisponibilidad::HORARIO,
                    'duracion_minutos' => 60,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                ]
            );
            if (! $servicio->reservable_id) {
                $servicio->updateQuietly(['reservable_id' => $recurso->id]);
            }

            $detalle = ReservaDetalle::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'reservable_id' => $recurso->id,
                ],
                [
                    'fecha_inicio' => $fechaReserva->toDateTimeString(),
                    'fecha_fin' => $fechaReserva->copy()->addHours(2)->toDateTimeString(),
                    'cantidad' => 1,
                    'adultos' => 2,
                    'ninos' => 0,
                    'precio_unitario' => $subtotal,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'impuestos' => $iva,
                    'estado' => $esPasada ? EstadoReservaDetalle::COMPLETADO : EstadoReservaDetalle::CONFIRMADO,
                ]
            );

            // 3. Huésped titular
            ReservaHuesped::query()->updateOrCreate(
                [
                    'reserva_detalle_id' => $detalle->id,
                    'es_titular' => true,
                ],
                [
                    'nombre' => $reserva->nombre_cliente,
                    'identificacion' => $cliente->persona->numero_identificacion ?? '001-010190-0001A',
                    'tipo_huesped' => TipoHuesped::ADULTO,
                    'email' => $reserva->email_cliente,
                    'telefono' => $reserva->telefono_cliente,
                ]
            );

            // 4. Historial
            ReservaEstadoHistorial::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'estado_nuevo' => $estado,
                ],
                [
                    'estado_anterior' => EstadoReserva::PENDIENTE,
                    'usuario_id' => $adminId,
                    'motivo' => 'Reserva de paquete/servicio agendada satisfactoriamente.',
                    'created_at' => $fechaReserva,
                ]
            );

            // 5. Cuentas y Facturas emitidas para pasadas
            if ($esPasada) {
                $numeroCuenta = 'CTA-PAQ-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);
                $cuenta = Cuenta::query()->updateOrCreate(
                    ['numero_cuenta' => $numeroCuenta],
                    [
                        'tipo_cuenta' => TipoCuenta::SERVICIO,
                        'estado' => EstadoCuenta::CERRADA,
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
                        'total_pagado' => $total,
                        'saldo' => 0.00,
                        'abierta_at' => $fechaReserva,
                        'cerrada_at' => $fechaReserva->copy()->addHours(2),
                        'abierta_por' => $adminId,
                        'cerrada_por' => $adminId,
                    ]
                );

                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => $reserva->notas ?? 'Servicio Spa Bugambilia'],
                    ['cantidad' => 1, 'precio_unitario' => $subtotal, 'subtotal' => $subtotal, 'total' => $subtotal, 'estado' => 1, 'creador_id' => $adminId]
                );

                PagoCuenta::query()->updateOrCreate(
                    [
                        'cuenta_id' => $cuenta->id,
                        'referencia_transaccion' => "PAQ-PAG-{$codigoReserva}",
                    ],
                    [
                        'forma_pago' => MetodoPago::TARJETA_CREDITO,
                        'moneda_id' => $monedaUsd->id,
                        'estado' => EstadoPago::APLICADO,
                        'monto' => $total,
                        'propina' => 0.00,
                        'observaciones' => 'Cobro 100% paquete con tarjeta BAC.',
                        'usuario_id' => $adminId,
                        'created_at' => $fechaReserva->copy()->addHours(2),
                    ]
                );

                $numeroVenta = 'VNT-PAQ-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);
                $venta = Venta::query()->updateOrCreate(
                    ['numero_venta' => $numeroVenta],
                    [
                        'cuenta_id' => $cuenta->id,
                        'cliente_id' => $cliente->id,
                        'moneda_id' => $monedaUsd->id,
                        'subtotal' => $subtotal,
                        'descuento_total' => 0.00,
                        'impuesto_total' => $iva,
                        'servicio_total' => 0.00,
                        'propina_total' => 0.00,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'estado' => EstadoVenta::Emitida,
                        'datos_fiscales' => [
                            'ruc' => 'J031000000000',
                            'razon_social' => $reserva->nombre_cliente,
                            'tipo_comprobante' => 'factura',
                        ],
                        'creada_por' => $adminId,
                        'created_at' => $fechaReserva->copy()->addHours(2),
                    ]
                );

                VentaDetalle::query()->updateOrCreate(
                    ['venta_id' => $venta->id, 'concepto' => $reserva->notas ?? 'Servicio de Spa y Paquete'],
                    ['cantidad' => 1, 'precio_unitario' => $subtotal, 'subtotal' => $subtotal, 'descuento' => 0.00, 'impuesto' => $iva, 'total_linea' => $subtotal]
                );

                if ($serieB && $authB) {
                    $correlativo = (int) $serieB->siguiente_numero;
                    $numeroFactura = $serieB->codigo.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);

                    $folio = FacturaFolio::query()->updateOrCreate(
                        [
                            'factura_serie_id' => $serieB->id,
                            'numero_correlativo' => $correlativo,
                        ],
                        [
                            'factura_autorizacion_dgi_id' => $authB->id,
                            'numero' => $numeroFactura,
                            'estado' => EstadoFolioFactura::Emitido,
                            'emitido_at' => $fechaReserva->copy()->addHours(2),
                        ]
                    );

                    $serieB->update(['siguiente_numero' => $correlativo + 1]);

                    $factura = Factura::query()->updateOrCreate(
                        [
                            'venta_id' => $venta->id,
                        ],
                        [
                            'factura_serie_id' => $serieB->id,
                            'factura_autorizacion_dgi_id' => $authB->id,
                            'cuenta_id' => $cuenta->id,
                            'cliente_id' => $cliente->id,
                            'tipo' => TipoFactura::Contado,
                            'estado' => EstadoFactura::Emitida,
                            'numero' => $numeroFactura,
                            'numero_correlativo' => $correlativo,
                            'fecha_emision' => $fechaReserva->copy()->addHours(2),
                            'moneda_id' => $monedaUsd->id,
                            'moneda_base_id' => $monedaNio->id,
                            'tasa_cambio' => $tasaCambio,
                            'subtotal' => $subtotal,
                            'descuento_total' => 0.00,
                            'iva_total' => $iva,
                            'servicio_total' => 0.00,
                            'propina_total' => 0.00,
                            'recargo_total' => 0.00,
                            'total' => $total,
                            'subtotal_base' => round($subtotal * $tasaCambio, 2),
                            'iva_total_base' => round($iva * $tasaCambio, 2),
                            'total_base' => round($total * $tasaCambio, 2),
                            'datos_receptor' => [
                                'nombre' => $reserva->nombre_cliente,
                                'identificacion' => $cliente->persona->numero_identificacion ?? '001-010190-0001A',
                                'email' => $reserva->email_cliente,
                                'direccion' => 'Nicaragua',
                            ],
                            'numero_autorizacion_dgi' => $authB->numero_autorizacion,
                            'rango_autorizado_desde' => (int) $authB->rango_desde,
                            'rango_autorizado_hasta' => (int) $authB->rango_hasta,
                            'hash_documento' => hash('sha256', $numeroFactura.'|'.$venta->id.'|'.$fechaReserva->toISOString()),
                            'emitida_por' => $adminId,
                        ]
                    );

                    $folio->update(['factura_id' => $factura->id]);

                    FacturaDetalle::query()->updateOrCreate(
                        [
                            'factura_id' => $factura->id,
                            'concepto' => $reserva->notas ?? 'Servicio de Spa y Paquete Turístico',
                        ],
                        [
                            'venta_detalle_id' => null,
                            'cantidad' => 1,
                            'precio_unitario' => $subtotal,
                            'subtotal' => $subtotal,
                            'descuento' => 0.00,
                            'iva_porcentaje' => 15.00,
                            'iva' => $iva,
                            'total_linea' => $total,
                        ]
                    );
                }
            }
        }

        $this->command->info('Reservas de servicios y paquetes sembradas con éxito.');
    }
}
