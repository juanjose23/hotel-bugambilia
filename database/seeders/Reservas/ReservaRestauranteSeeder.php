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
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class ReservaRestauranteSeeder extends Seeder
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
        $mesas = Espacio::query()
            ->where('codigo', 'like', 'MESA-%')
            ->orWhere('codigo', 'REST-001')
            ->get();

        if ($clientes->isEmpty() || $mesas->isEmpty()) {
            return;
        }

        $serieB = FacturaSerie::query()->where('codigo', 'B')->first() ?? FacturaSerie::query()->first();
        $authB = $serieB ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $serieB->id)->first() : null;
        $tasaCambio = 36.50;

        // Sembrar 20 reservas de restaurante (pasadas atendidas, activas hoy y futuras)
        for ($i = 1; $i <= 20; $i++) {
            $cliente = $clientes->get(($i * 3 + 2) % $clientes->count());
            $mesa = $mesas->get($i % $mesas->count());

            if (! $cliente || ! $mesa) {
                continue;
            }

            $esPasada = ($i <= 14);
            $esActivaHoy = ($i >= 15 && $i <= 17);
            $esFutura = ($i >= 18);

            if ($esPasada) {
                $fechaReserva = Carbon::today()->subDays($i * 4)->setTime(19, 0);
                $estado = EstadoReserva::CHECKED_OUT;
            } elseif ($esActivaHoy) {
                $fechaReserva = Carbon::today()->setTime(13, 0);
                $estado = EstadoReserva::CHECKED_IN;
            } else {
                $fechaReserva = Carbon::today()->addDays(($i - 17) * 5)->setTime(20, 0);
                $estado = EstadoReserva::CONFIRMADA;
            }

            $comensales = ($i % 3 === 0) ? 4 : (($i % 2 === 0) ? 2 : 6);
            $consumoPorPersona = 18.50 + (($i * 2) % 15);
            $subtotal = round($comensales * $consumoPorPersona, 2);
            $iva = round($subtotal * 0.15, 2);
            $propina = round($subtotal * 0.10, 2);
            $total = round($subtotal + $iva + $propina, 2);

            $totalPagado = $esPasada ? $total : ($esFutura ? round($total * 0.30, 2) : 0.00);
            $saldo = round($total - $totalPagado, 2);

            $codigoReserva = 'RES-REST-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            // 1. Crear Reserva
            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Comensal Restaurante',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 8888-9999',
                    'email_cliente' => "restaurante_{$cliente->id}@hotel.com",
                    'tipo_reserva' => TipoReserva::RESTAURANTE,
                    'espacio_id' => $mesa->id,
                    'fecha_check_in' => $fechaReserva->toDateString(),
                    'fecha_check_out' => $fechaReserva->toDateString(),
                    'hora_reserva' => $fechaReserva->format('H:i'),
                    'adultos' => $comensales,
                    'ninos' => 0,
                    'solicita_cuenta' => true,
                    'limite_cuenta_solicitado' => 300.00,
                    'estado' => $estado,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'total' => $total,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => $esPasada ? TipoPagoReserva::PAGO_COMPLETO : ($esFutura ? TipoPagoReserva::ABONO_50 : TipoPagoReserva::SIN_PAGO),
                    'total_pagado' => $totalPagado,
                    'saldo' => $saldo,
                    'notas' => "Reserva gastronómica para {$comensales} personas en {$mesa->nombre}.",
                ]
            );

            // 2. Recurso Reservable y Detalle
            $recurso = RecursoReservable::firstOrCreate(
                ['nombre' => $mesa->nombre, 'tipo' => TipoRecursoReservable::ESPACIO],
                [
                    'capacidad' => $mesa->capacidad_personas ?? 4,
                    'control_disponibilidad' => ControlDisponibilidad::HORARIO,
                    'duracion_minutos' => 120,
                    'estado' => EstadoRecursoReservable::ACTIVO,
                ]
            );
            if (! $mesa->reservable_id) {
                $mesa->updateQuietly(['reservable_id' => $recurso->id]);
            }

            $detalleReserva = ReservaDetalle::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'reservable_id' => $recurso->id,
                ],
                [
                    'fecha_inicio' => $fechaReserva->toDateTimeString(),
                    'fecha_fin' => $fechaReserva->copy()->addHours(2)->toDateTimeString(),
                    'cantidad' => 1,
                    'adultos' => $comensales,
                    'ninos' => 0,
                    'precio_unitario' => $subtotal,
                    'subtotal' => $subtotal,
                    'descuento' => 0.00,
                    'impuestos' => $iva,
                    'estado' => $esPasada ? EstadoReservaDetalle::COMPLETADO : ($esActivaHoy ? EstadoReservaDetalle::EN_USO : EstadoReservaDetalle::CONFIRMADO),
                ]
            );

            // 3. Huésped / Comensal titular
            ReservaHuesped::query()->updateOrCreate(
                [
                    'reserva_detalle_id' => $detalleReserva->id,
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
                    'motivo' => "Reserva de mesa procesada en estado {$estado->getLabel()}.",
                    'created_at' => $fechaReserva,
                ]
            );

            // 5. Cuentas y Facturas para las atendidas pasadas
            if ($esPasada) {
                $numeroCuenta = 'CTA-REST-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);
                $cuenta = Cuenta::query()->updateOrCreate(
                    ['numero_cuenta' => $numeroCuenta],
                    [
                        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
                        'estado' => EstadoCuenta::CERRADA,
                        'cliente_id' => $cliente->id,
                        'reserva_id' => $reserva->id,
                        'moneda_id' => $monedaUsd->id,
                        'limite_autorizado' => 300.00,
                        'subtotal' => $subtotal,
                        'descuento_total' => 0.00,
                        'impuesto_total' => $iva,
                        'cargo_servicio_total' => 0.00,
                        'propina_total' => $propina,
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
                    [
                        'cuenta_id' => $cuenta->id,
                        'concepto' => "Servicio Gastronómico Restaurante ({$comensales} personas)",
                    ],
                    [
                        'cantidad' => $comensales,
                        'precio_unitario' => $consumoPorPersona,
                        'subtotal' => $subtotal,
                        'total' => $subtotal,
                        'estado' => 1,
                        'creador_id' => $adminId,
                    ]
                );

                PagoCuenta::query()->updateOrCreate(
                    [
                        'cuenta_id' => $cuenta->id,
                        'referencia_transaccion' => "REST-PAG-{$codigoReserva}",
                    ],
                    [
                        'forma_pago' => ($i % 2 === 0) ? MetodoPago::TARJETA_CREDITO : MetodoPago::EFECTIVO,
                        'moneda_id' => $monedaUsd->id,
                        'estado' => EstadoPago::APLICADO,
                        'monto' => $total,
                        'propina' => $propina,
                        'observaciones' => 'Pago de consumo total en mesa restaurante.',
                        'usuario_id' => $adminId,
                        'created_at' => $fechaReserva->copy()->addHours(2),
                    ]
                );

                $numeroVenta = 'VNT-REST-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);
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
                        'propina_total' => $propina,
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

                $ventaDetalle = VentaDetalle::query()->updateOrCreate(
                    [
                        'venta_id' => $venta->id,
                        'concepto' => "Servicio de Restaurante ({$comensales} personas) - {$mesa->nombre}",
                    ],
                    [
                        'cantidad' => $comensales,
                        'precio_unitario' => $consumoPorPersona,
                        'subtotal' => $subtotal,
                        'descuento' => 0.00,
                        'impuesto' => $iva,
                        'total_linea' => $subtotal,
                    ]
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
                            'propina_total' => $propina,
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
                            'concepto' => "Servicio de Restaurante ({$comensales} personas) - {$mesa->nombre}",
                        ],
                        [
                            'venta_detalle_id' => $ventaDetalle->id,
                            'cantidad' => $comensales,
                            'precio_unitario' => $consumoPorPersona,
                            'subtotal' => $subtotal,
                            'descuento' => 0.00,
                            'iva_porcentaje' => 15.00,
                            'iva' => $iva,
                            'total_linea' => $subtotal,
                        ]
                    );
                }
            } elseif ($esActivaHoy) {
                $numeroCuenta = 'CTA-REST-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);
                $cuenta = Cuenta::query()->updateOrCreate(
                    ['numero_cuenta' => $numeroCuenta],
                    [
                        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
                        'estado' => EstadoCuenta::ABIERTA,
                        'cliente_id' => $cliente->id,
                        'reserva_id' => $reserva->id,
                        'moneda_id' => $monedaUsd->id,
                        'limite_autorizado' => 300.00,
                        'subtotal' => $subtotal,
                        'descuento_total' => 0.00,
                        'impuesto_total' => $iva,
                        'cargo_servicio_total' => 0.00,
                        'propina_total' => $propina,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'total_pagado' => 0.00,
                        'saldo' => $total,
                        'abierta_at' => $fechaReserva,
                        'abierta_por' => $adminId,
                    ]
                );

                CuentaDetalle::query()->updateOrCreate(
                    [
                        'cuenta_id' => $cuenta->id,
                        'concepto' => "Consumo Mesa Activa ({$comensales} personas) - {$mesa->nombre}",
                    ],
                    [
                        'cantidad' => $comensales,
                        'precio_unitario' => $consumoPorPersona,
                        'subtotal' => $subtotal,
                        'total' => $subtotal,
                        'estado' => 1,
                        'creador_id' => $adminId,
                    ]
                );
            }
        }

        $this->command->info('Reservas de restaurante y mesas sembradas con éxito.');
    }
}
