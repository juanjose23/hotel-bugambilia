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
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class ReservaEspacioSeeder extends Seeder
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

        // Obtener clientes corporativos / jurídicos con RUC
        $clientesCorporativos = Cliente::query()
            ->whereHas('persona', fn ($q) => $q->where('tipo_persona', 'juridica'))
            ->with(['persona.personaJuridica'])
            ->get();

        if ($clientesCorporativos->isEmpty()) {
            $clientesCorporativos = Cliente::query()->with('persona')->get();
        }

        $salonEventos = Espacio::query()
            ->where('codigo', 'like', 'SAL-%')
            ->orWhere('nombre', 'like', '%Salón%')
            ->first() ?? Espacio::query()->first();

        if (! $salonEventos) {
            return;
        }

        $serieA = FacturaSerie::query()->where('codigo', 'A')->first() ?? FacturaSerie::query()->first();
        $authA = $serieA ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $serieA->id)->first() : null;
        $tasaCambio = 36.50;

        $eventos = [
            ['titulo' => 'Seminario Anual de Planificación Financiera 2026', 'participantes' => 45, 'diasAtras' => 60, 'estado' => EstadoReserva::CHECKED_OUT],
            ['titulo' => 'Capacitación Ejecutiva en Liderazgo y Ventas', 'participantes' => 30, 'diasAtras' => 45, 'estado' => EstadoReserva::CHECKED_OUT],
            ['titulo' => 'Taller Regional de Auditoría y Cumplimiento DGI', 'participantes' => 50, 'diasAtras' => 30, 'estado' => EstadoReserva::CHECKED_OUT],
            ['titulo' => 'Lanzamiento de Nueva Línea de Servicios Turísticos', 'participantes' => 60, 'diasAtras' => 15, 'estado' => EstadoReserva::CHECKED_OUT],
            ['titulo' => 'Convención de Constructores del Norte de Nicaragua', 'participantes' => 70, 'diasAtras' => 5, 'estado' => EstadoReserva::CHECKED_OUT],
            ['titulo' => 'Reunión de Directorio Corporativo y Almuerzo de Gala', 'participantes' => 20, 'diasAtras' => 0, 'estado' => EstadoReserva::CHECKED_IN],
            ['titulo' => 'Foro Internacional de Comercio y Logística', 'participantes' => 80, 'diasAtras' => -15, 'estado' => EstadoReserva::CONFIRMADA],
            ['titulo' => 'Simposio Centroamericano de Transformación Digital', 'participantes' => 65, 'diasAtras' => -30, 'estado' => EstadoReserva::CONFIRMADA],
            ['titulo' => 'Asamblea General Ordinaria de Accionistas', 'participantes' => 40, 'diasAtras' => -45, 'estado' => EstadoReserva::CONFIRMADA],
            ['titulo' => 'Jornada de Integración Corporativa y Banquete', 'participantes' => 55, 'diasAtras' => -60, 'estado' => EstadoReserva::PENDIENTE],
        ];

        $recursoSalon = RecursoReservable::firstOrCreate(
            ['nombre' => $salonEventos->nombre, 'tipo' => TipoRecursoReservable::ESPACIO],
            [
                'capacidad' => $salonEventos->capacidad_personas ?? 100,
                'control_disponibilidad' => ControlDisponibilidad::HORARIO,
                'duracion_minutos' => 480,
                'estado' => EstadoRecursoReservable::ACTIVO,
            ]
        );
        if (! $salonEventos->reservable_id) {
            $salonEventos->updateQuietly(['reservable_id' => $recursoSalon->id]);
        }

        foreach ($eventos as $index => $evento) {
            $cliente = $clientesCorporativos->get($index % $clientesCorporativos->count());

            if (! $cliente) {
                continue;
            }

            $fechaEvento = Carbon::today()->subDays($evento['diasAtras'])->setTime(8, 30);
            $esPasado = $evento['estado'] === EstadoReserva::CHECKED_OUT;
            $esHoy = $evento['estado'] === EstadoReserva::CHECKED_IN;
            $esFuturo = in_array($evento['estado'], [EstadoReserva::CONFIRMADA, EstadoReserva::PENDIENTE], true);

            $costoAlquilerSalon = 350.00;
            $costoBanquete = round($evento['participantes'] * 16.50, 2);
            $costoCoffeeBreak = round($evento['participantes'] * 6.50, 2);
            $subtotal = round($costoAlquilerSalon + $costoBanquete + $costoCoffeeBreak, 2);
            $descuentoCorp = round($subtotal * 0.10, 2); // Convenio 10% OFF
            $baseGravable = $subtotal - $descuentoCorp;
            $iva = round($baseGravable * 0.15, 2);
            $total = round($baseGravable + $iva, 2);

            $totalPagado = $esPasado ? $total : ($esFuturo && $evento['estado'] === EstadoReserva::CONFIRMADA ? round($total * 0.50, 2) : 0.00);
            $saldo = round($total - $totalPagado, 2);

            $codigoReserva = 'RES-ESP-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            // 1. Crear Reserva de Espacio / Salón
            $reserva = Reserva::query()->updateOrCreate(
                ['codigo_reserva' => $codigoReserva],
                [
                    'cliente_id' => $cliente->id,
                    'nombre_cliente' => $cliente->persona->nombre_completo ?? 'Empresa Corporativa',
                    'telefono_cliente' => $cliente->persona->telefono ?? '+505 2278-9000',
                    'email_cliente' => "eventos_{$cliente->id}@hotel.com",
                    'tipo_reserva' => TipoReserva::RESTAURANTE,
                    'espacio_id' => $salonEventos->id,
                    'fecha_check_in' => $fechaEvento->toDateString(),
                    'fecha_check_out' => $fechaEvento->toDateString(),
                    'hora_reserva' => '08:30',
                    'adultos' => $evento['participantes'],
                    'ninos' => 0,
                    'solicita_cuenta' => true,
                    'limite_cuenta_solicitado' => 2000.00,
                    'estado' => $evento['estado'],
                    'subtotal' => $subtotal,
                    'descuento' => $descuentoCorp,
                    'total' => $total,
                    'moneda_id' => $monedaUsd->id,
                    'tipo_pago' => $esPasado ? TipoPagoReserva::PAGO_COMPLETO : ($totalPagado > 0 ? TipoPagoReserva::ABONO_50 : TipoPagoReserva::SIN_PAGO),
                    'total_pagado' => $totalPagado,
                    'saldo' => $saldo,
                    'notas' => "Evento corporativo: {$evento['titulo']} con montaje auditorio y servicio de banquetes.",
                ]
            );

            // 2. Detalle
            $detalle = ReservaDetalle::query()->updateOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'reservable_id' => $recursoSalon->id,
                ],
                [
                    'fecha_inicio' => $fechaEvento->toDateTimeString(),
                    'fecha_fin' => $fechaEvento->copy()->addHours(8)->toDateTimeString(),
                    'cantidad' => 1,
                    'adultos' => $evento['participantes'],
                    'ninos' => 0,
                    'precio_unitario' => $costoAlquilerSalon,
                    'subtotal' => $subtotal,
                    'descuento' => $descuentoCorp,
                    'impuestos' => $iva,
                    'estado' => $esPasado ? EstadoReservaDetalle::COMPLETADO : ($esHoy ? EstadoReservaDetalle::EN_USO : EstadoReservaDetalle::CONFIRMADO),
                ]
            );

            $persona = $cliente->persona;
            $juridica = $persona instanceof Persona ? $persona->personaJuridica : null;
            $ruc = $juridica ? $juridica->numero_identificacion : 'J0310000012345';
            $razonSocial = $juridica ? $juridica->razon_social : $reserva->nombre_cliente;

            // 3. Huésped titular
            ReservaHuesped::query()->updateOrCreate(
                [
                    'reserva_detalle_id' => $detalle->id,
                    'es_titular' => true,
                ],
                [
                    'nombre' => $reserva->nombre_cliente,
                    'identificacion' => $ruc,
                    'tipo_huesped' => TipoHuesped::ADULTO,
                    'email' => $reserva->email_cliente,
                    'telefono' => $reserva->telefono_cliente,
                ]
            );

            // 4. Historial
            ReservaEstadoHistorial::query()->firstOrCreate(
                [
                    'reserva_id' => $reserva->id,
                    'estado_nuevo' => $evento['estado'],
                ],
                [
                    'estado_anterior' => EstadoReserva::PENDIENTE,
                    'usuario_id' => $adminId,
                    'motivo' => "Evento corporativo programado: {$evento['titulo']}.",
                    'created_at' => $fechaEvento,
                ]
            );

            // 5. Cuentas y Facturas con Crédito Fiscal (Serie A) para eventos pasados
            if ($esPasado) {
                $numeroCuenta = 'CTA-EVT-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);
                $cuenta = Cuenta::query()->updateOrCreate(
                    ['numero_cuenta' => $numeroCuenta],
                    [
                        'tipo_cuenta' => TipoCuenta::SERVICIO,
                        'estado' => EstadoCuenta::CERRADA,
                        'cliente_id' => $cliente->id,
                        'reserva_id' => $reserva->id,
                        'moneda_id' => $monedaUsd->id,
                        'limite_autorizado' => 2500.00,
                        'subtotal' => $subtotal,
                        'descuento_total' => $descuentoCorp,
                        'impuesto_total' => $iva,
                        'cargo_servicio_total' => 0.00,
                        'propina_total' => 0.00,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'total_pagado' => $total,
                        'saldo' => 0.00,
                        'abierta_at' => $fechaEvento,
                        'cerrada_at' => $fechaEvento->copy()->addHours(8),
                        'abierta_por' => $adminId,
                        'cerrada_por' => $adminId,
                    ]
                );

                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => "Alquiler Salón de Eventos - {$evento['titulo']}"],
                    ['cantidad' => 1, 'precio_unitario' => $costoAlquilerSalon, 'subtotal' => $costoAlquilerSalon, 'total' => $costoAlquilerSalon, 'estado' => 1, 'creador_id' => $adminId]
                );
                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => "Servicio de Banquete Buffet ({$evento['participantes']} personas)"],
                    ['cantidad' => $evento['participantes'], 'precio_unitario' => 16.50, 'subtotal' => $costoBanquete, 'total' => $costoBanquete, 'estado' => 1, 'creador_id' => $adminId]
                );
                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => "Coffee Break Continuo ({$evento['participantes']} personas)"],
                    ['cantidad' => $evento['participantes'], 'precio_unitario' => 6.50, 'subtotal' => $costoCoffeeBreak, 'total' => $costoCoffeeBreak, 'estado' => 1, 'creador_id' => $adminId]
                );

                PagoCuenta::query()->updateOrCreate(
                    [
                        'cuenta_id' => $cuenta->id,
                        'referencia_transaccion' => "TRANSF-CORP-{$codigoReserva}",
                    ],
                    [
                        'forma_pago' => MetodoPago::TRANSFERENCIA,
                        'moneda_id' => $monedaUsd->id,
                        'estado' => EstadoPago::APLICADO,
                        'monto' => $total,
                        'propina' => 0.00,
                        'observaciones' => 'Transferencia bancaria corporativa Lafise 100% liquidada.',
                        'usuario_id' => $adminId,
                        'created_at' => $fechaEvento->copy()->addHours(8),
                    ]
                );

                $numeroVenta = 'VNT-EVT-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);
                $venta = Venta::query()->updateOrCreate(
                    ['numero_venta' => $numeroVenta],
                    [
                        'cuenta_id' => $cuenta->id,
                        'cliente_id' => $cliente->id,
                        'moneda_id' => $monedaUsd->id,
                        'subtotal' => $subtotal,
                        'descuento_total' => $descuentoCorp,
                        'impuesto_total' => $iva,
                        'servicio_total' => 0.00,
                        'propina_total' => 0.00,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'estado' => EstadoVenta::Emitida,
                        'datos_fiscales' => [
                            'ruc' => $ruc,
                            'razon_social' => $razonSocial,
                            'tipo_comprobante' => 'factura_credito_fiscal',
                        ],
                        'creada_por' => $adminId,
                        'created_at' => $fechaEvento->copy()->addHours(8),
                    ]
                );

                VentaDetalle::query()->updateOrCreate(
                    ['venta_id' => $venta->id, 'concepto' => "Alquiler Salón de Eventos ({$evento['titulo']})"],
                    ['cantidad' => 1, 'precio_unitario' => $costoAlquilerSalon, 'subtotal' => $costoAlquilerSalon, 'descuento' => 0.00, 'impuesto' => round($costoAlquilerSalon * 0.15, 2), 'total_linea' => $costoAlquilerSalon]
                );
                VentaDetalle::query()->updateOrCreate(
                    ['venta_id' => $venta->id, 'concepto' => "Servicio de Banquete y Coffee Break ({$evento['participantes']} personas)"],
                    ['cantidad' => $evento['participantes'], 'precio_unitario' => 23.00, 'subtotal' => $costoBanquete + $costoCoffeeBreak, 'descuento' => $descuentoCorp, 'impuesto' => round(($costoBanquete + $costoCoffeeBreak - $descuentoCorp) * 0.15, 2), 'total_linea' => $costoBanquete + $costoCoffeeBreak - $descuentoCorp]
                );

                if ($serieA && $authA) {
                    $correlativo = (int) $serieA->siguiente_numero;
                    $numeroFactura = $serieA->codigo.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);

                    $folio = FacturaFolio::query()->updateOrCreate(
                        [
                            'factura_serie_id' => $serieA->id,
                            'numero_correlativo' => $correlativo,
                        ],
                        [
                            'factura_autorizacion_dgi_id' => $authA->id,
                            'numero' => $numeroFactura,
                            'estado' => EstadoFolioFactura::Emitido,
                            'emitido_at' => $fechaEvento->copy()->addHours(8),
                        ]
                    );

                    $serieA->update(['siguiente_numero' => $correlativo + 1]);

                    $factura = Factura::query()->updateOrCreate(
                        [
                            'venta_id' => $venta->id,
                        ],
                        [
                            'factura_serie_id' => $serieA->id,
                            'factura_autorizacion_dgi_id' => $authA->id,
                            'cuenta_id' => $cuenta->id,
                            'cliente_id' => $cliente->id,
                            'tipo' => TipoFactura::Credito,
                            'estado' => EstadoFactura::Emitida,
                            'numero' => $numeroFactura,
                            'numero_correlativo' => $correlativo,
                            'fecha_emision' => $fechaEvento->copy()->addHours(8),
                            'moneda_id' => $monedaUsd->id,
                            'moneda_base_id' => $monedaNio->id,
                            'tasa_cambio' => $tasaCambio,
                            'subtotal' => $subtotal,
                            'descuento_total' => $descuentoCorp,
                            'iva_total' => $iva,
                            'servicio_total' => 0.00,
                            'propina_total' => 0.00,
                            'recargo_total' => 0.00,
                            'total' => $total,
                            'subtotal_base' => round($subtotal * $tasaCambio, 2),
                            'iva_total_base' => round($iva * $tasaCambio, 2),
                            'total_base' => round($total * $tasaCambio, 2),
                            'datos_receptor' => [
                                'nombre' => $razonSocial,
                                'identificacion' => $ruc,
                                'email' => $reserva->email_cliente,
                                'direccion' => $cliente->persona->direccion ?? 'Managua, Nicaragua',
                            ],
                            'numero_autorizacion_dgi' => $authA->numero_autorizacion,
                            'rango_autorizado_desde' => (int) $authA->rango_desde,
                            'rango_autorizado_hasta' => (int) $authA->rango_hasta,
                            'hash_documento' => hash('sha256', $numeroFactura.'|'.$venta->id.'|'.$fechaEvento->toISOString()),
                            'emitida_por' => $adminId,
                        ]
                    );

                    $folio->update(['factura_id' => $factura->id]);

                    FacturaDetalle::query()->updateOrCreate(
                        [
                            'factura_id' => $factura->id,
                            'concepto' => "Alquiler Salón de Eventos y Banquetes ({$evento['titulo']})",
                        ],
                        [
                            'venta_detalle_id' => null,
                            'cantidad' => 1,
                            'precio_unitario' => $subtotal,
                            'subtotal' => $subtotal,
                            'descuento' => $descuentoCorp,
                            'iva_porcentaje' => 15.00,
                            'iva' => $iva,
                            'total_linea' => $total,
                        ]
                    );
                }
            } elseif ($esHoy) {
                $numeroCuenta = 'CTA-EVT-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);
                $cuenta = Cuenta::query()->updateOrCreate(
                    ['numero_cuenta' => $numeroCuenta],
                    [
                        'tipo_cuenta' => TipoCuenta::SERVICIO,
                        'estado' => EstadoCuenta::ABIERTA,
                        'cliente_id' => $cliente->id,
                        'reserva_id' => $reserva->id,
                        'moneda_id' => $monedaUsd->id,
                        'limite_autorizado' => 2500.00,
                        'subtotal' => $subtotal,
                        'descuento_total' => $descuentoCorp,
                        'impuesto_total' => $iva,
                        'cargo_servicio_total' => 0.00,
                        'propina_total' => 0.00,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'total_pagado' => 0.00,
                        'saldo' => $total,
                        'abierta_at' => $fechaEvento,
                        'abierta_por' => $adminId,
                    ]
                );

                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => "Alquiler Salón de Eventos - {$evento['titulo']}"],
                    ['cantidad' => 1, 'precio_unitario' => $costoAlquilerSalon, 'subtotal' => $costoAlquilerSalon, 'total' => $costoAlquilerSalon, 'estado' => 1, 'creador_id' => $adminId]
                );
                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => "Servicio de Banquete Buffet ({$evento['participantes']} personas)"],
                    ['cantidad' => $evento['participantes'], 'precio_unitario' => 16.50, 'subtotal' => $costoBanquete, 'total' => $costoBanquete, 'estado' => 1, 'creador_id' => $adminId]
                );
                CuentaDetalle::query()->updateOrCreate(
                    ['cuenta_id' => $cuenta->id, 'concepto' => "Coffee Break Continuo ({$evento['participantes']} personas)"],
                    ['cantidad' => $evento['participantes'], 'precio_unitario' => 6.50, 'subtotal' => $costoCoffeeBreak, 'total' => $costoCoffeeBreak, 'estado' => 1, 'creador_id' => $adminId]
                );
            }
        }

        $this->command->info('Reservas de salón de eventos corporativos sembradas con éxito.');
    }
}
