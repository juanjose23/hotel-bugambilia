<?php

declare(strict_types=1);

namespace Database\Seeders\Reservas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\EstadoFolioFactura;
use App\Enums\Facturacion\TipoFactura;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaDetalle;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class FacturacionBorradorYVentasSeeder extends Seeder
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

        $clientes = Cliente::query()->with(['persona.personaJuridica'])->get();
        if ($clientes->isEmpty()) {
            return;
        }

        $serieA = FacturaSerie::query()->where('codigo', 'A')->first();
        $serieB = FacturaSerie::query()->where('codigo', 'B')->first() ?? $serieA;

        $authA = $serieA ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $serieA->id)->first() : null;
        $authB = $serieB ? FacturaAutorizacionDgi::query()->where('factura_serie_id', $serieB->id)->first() : $authA;

        $tasaCambio = 36.50;

        // ─── 1. Facturas en Borrador (Proformas listas para revisión y emisión en Filament) ───
        $borradores = [
            ['concepto' => 'Proforma Hospedaje Suite Presidencial (3 Noches)', 'subtotal' => 450.00, 'cliente_idx' => 1],
            ['concepto' => 'Proforma Alquiler Salón para Conferencia Médica', 'subtotal' => 520.00, 'cliente_idx' => 2],
            ['concepto' => 'Borrador Consumo Banquete Corporativo (35 comensales)', 'subtotal' => 680.00, 'cliente_idx' => 3],
            ['concepto' => 'Proforma Paquete Corporativo Hospedaje + Restaurante', 'subtotal' => 390.00, 'cliente_idx' => 4],
            ['concepto' => 'Borrador Servicios Spa & Bienestar Ejecutivo', 'subtotal' => 210.00, 'cliente_idx' => 5],
            ['concepto' => 'Proforma Estancia Ejecutiva Prolongada (5 Noches)', 'subtotal' => 750.00, 'cliente_idx' => 6],
        ];

        foreach ($borradores as $idx => $item) {
            $cliente = $clientes->get($item['cliente_idx'] % $clientes->count());
            if (! $cliente) {
                continue;
            }

            $iva = round($item['subtotal'] * 0.15, 2);
            $total = round($item['subtotal'] + $iva, 2);
            $numeroCuenta = 'CTA-BORR-'.str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT);

            $cuenta = Cuenta::query()->updateOrCreate(
                ['numero_cuenta' => $numeroCuenta],
                [
                    'tipo_cuenta' => TipoCuenta::SERVICIO,
                    'estado' => EstadoCuenta::ABIERTA,
                    'cliente_id' => $cliente->id,
                    'moneda_id' => $monedaUsd->id,
                    'limite_autorizado' => 1000.00,
                    'subtotal' => $item['subtotal'],
                    'descuento_total' => 0.00,
                    'impuesto_total' => $iva,
                    'cargo_servicio_total' => 0.00,
                    'propina_total' => 0.00,
                    'recargo_total' => 0.00,
                    'total' => $total,
                    'total_pagado' => 0.00,
                    'saldo' => $total,
                    'abierta_at' => Carbon::today()->setTime(10, 0),
                    'abierta_por' => $adminId,
                ]
            );

            $correlativoBorrador = 90000 + $idx + 1;
            $serieCodigo = $serieA instanceof FacturaSerie ? $serieA->codigo : 'A';
            $serieId = $serieA instanceof FacturaSerie ? $serieA->id : 1;
            $numeroFactura = $serieCodigo.'-BORR-'.str_pad((string) ($idx + 1), 6, '0', STR_PAD_LEFT);

            $factura = Factura::query()->updateOrCreate(
                ['numero' => $numeroFactura],
                [
                    'factura_serie_id' => $serieId,
                    'factura_autorizacion_dgi_id' => $authA?->id,
                    'cuenta_id' => $cuenta->id,
                    'cliente_id' => $cliente->id,
                    'tipo' => TipoFactura::Contado,
                    'estado' => EstadoFactura::Borrador,
                    'numero_correlativo' => $correlativoBorrador,
                    'fecha_emision' => Carbon::today()->setTime(10, 30),
                    'moneda_id' => $monedaUsd->id,
                    'moneda_base_id' => $monedaNio->id,
                    'tasa_cambio' => $tasaCambio,
                    'subtotal' => $item['subtotal'],
                    'descuento_total' => 0.00,
                    'iva_total' => $iva,
                    'servicio_total' => 0.00,
                    'propina_total' => 0.00,
                    'recargo_total' => 0.00,
                    'total' => $total,
                    'subtotal_base' => round($item['subtotal'] * $tasaCambio, 2),
                    'iva_total_base' => round($iva * $tasaCambio, 2),
                    'total_base' => round($total * $tasaCambio, 2),
                    'datos_receptor' => [
                        'nombre' => $cliente->persona->nombre_completo ?? 'Cliente Proforma',
                        'identificacion' => $cliente->persona->numero_identificacion ?? '001-010190-0001A',
                        'email' => "cliente_{$cliente->id}@email.com",
                        'direccion' => 'Nicaragua',
                    ],
                    'emitida_por' => $adminId,
                ]
            );

            FacturaDetalle::query()->updateOrCreate(
                ['factura_id' => $factura->id, 'concepto' => $item['concepto']],
                [
                    'cantidad' => 1,
                    'precio_unitario' => $item['subtotal'],
                    'subtotal' => $item['subtotal'],
                    'descuento' => 0.00,
                    'iva_porcentaje' => 15.00,
                    'iva' => $iva,
                    'total_linea' => $total,
                ]
            );
        }

        // ─── 2. Facturas Anuladas con Trazabilidad y Motivo DGI ───
        $anulaciones = [
            ['concepto' => 'Consumo Restaurante Almuerzo Ejecutivo', 'subtotal' => 85.00, 'motivo' => 'Error en datos fiscales / RUC de la empresa receptora.'],
            ['concepto' => 'Hospedaje 1 Noche Habitación Sencilla', 'subtotal' => 65.00, 'motivo' => 'Cambio de titular y forma de pago a solicitud del huésped.'],
            ['concepto' => 'Servicio Masaje Relajante Parejas', 'subtotal' => 90.00, 'motivo' => 'Duplicidad involuntaria de factura al emitir en caja.'],
            ['concepto' => 'Alquiler Mobiliario Adicional para Evento', 'subtotal' => 120.00, 'motivo' => 'Cancelación de ítem no utilizado durante el evento.'],
            ['concepto' => 'Consumo Bar & Terraza Bugambilia', 'subtotal' => 55.00, 'motivo' => 'Reimpresión con desglose separado por cuentas individuales.'],
        ];

        foreach ($anulaciones as $idx => $anulada) {
            $cliente = $clientes->get(($idx + 3) % $clientes->count());
            if (! $cliente) {
                continue;
            }

            $iva = round($anulada['subtotal'] * 0.15, 2);
            $total = round($anulada['subtotal'] + $iva, 2);
            $fechaAnulacion = Carbon::today()->subDays(($idx + 1) * 7)->setTime(14, 0);

            $numeroVenta = 'VNT-ANUL-'.str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT);
            $venta = Venta::query()->updateOrCreate(
                ['numero_venta' => $numeroVenta],
                [
                    'cliente_id' => $cliente->id,
                    'moneda_id' => $monedaUsd->id,
                    'subtotal' => $anulada['subtotal'],
                    'descuento_total' => 0.00,
                    'impuesto_total' => $iva,
                    'servicio_total' => 0.00,
                    'propina_total' => 0.00,
                    'recargo_total' => 0.00,
                    'total' => $total,
                    'estado' => EstadoVenta::Anulada,
                    'datos_fiscales' => ['motivo_anulacion' => $anulada['motivo']],
                    'creada_por' => $adminId,
                    'anulada_por' => $adminId,
                    'anulada_en' => $fechaAnulacion,
                    'created_at' => $fechaAnulacion->copy()->subHours(1),
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
                        'estado' => EstadoFolioFactura::Anulado,
                        'emitido_at' => $fechaAnulacion->copy()->subHours(1),
                        'anulado_at' => $fechaAnulacion,
                    ]
                );

                $serieB->update(['siguiente_numero' => $correlativo + 1]);

                $factura = Factura::query()->updateOrCreate(
                    ['venta_id' => $venta->id],
                    [
                        'factura_serie_id' => $serieB->id,
                        'factura_autorizacion_dgi_id' => $authB->id,
                        'cliente_id' => $cliente->id,
                        'tipo' => TipoFactura::Contado,
                        'estado' => EstadoFactura::Anulada,
                        'numero' => $numeroFactura,
                        'numero_correlativo' => $correlativo,
                        'fecha_emision' => $fechaAnulacion->copy()->subHours(1),
                        'moneda_id' => $monedaUsd->id,
                        'moneda_base_id' => $monedaNio->id,
                        'tasa_cambio' => $tasaCambio,
                        'subtotal' => $anulada['subtotal'],
                        'descuento_total' => 0.00,
                        'iva_total' => $iva,
                        'servicio_total' => 0.00,
                        'propina_total' => 0.00,
                        'recargo_total' => 0.00,
                        'total' => $total,
                        'subtotal_base' => round($anulada['subtotal'] * $tasaCambio, 2),
                        'iva_total_base' => round($iva * $tasaCambio, 2),
                        'total_base' => round($total * $tasaCambio, 2),
                        'datos_receptor' => [
                            'nombre' => $cliente->persona->nombre_completo ?? 'Cliente',
                            'identificacion' => $cliente->persona->numero_identificacion ?? '001-010190-0001A',
                            'email' => "cliente_{$cliente->id}@email.com",
                        ],
                        'motivo_anulacion' => $anulada['motivo'],
                        'anulada_at' => $fechaAnulacion,
                        'anulada_por' => $adminId,
                        'emitida_por' => $adminId,
                    ]
                );

                $folio->update(['factura_id' => $factura->id]);

                FacturaDetalle::query()->updateOrCreate(
                    ['factura_id' => $factura->id, 'concepto' => $anulada['concepto']],
                    [
                        'cantidad' => 1,
                        'precio_unitario' => $anulada['subtotal'],
                        'subtotal' => $anulada['subtotal'],
                        'descuento' => 0.00,
                        'iva_porcentaje' => 15.00,
                        'iva' => $iva,
                        'total_linea' => $total,
                    ]
                );
            }
        }

        // ─── 3. Ventas Directas de Mostrador POS (Restaurante / Bar sin alojamiento) ───
        for ($pos = 1; $pos <= 15; $pos++) {
            $cliente = $clientes->get(($pos * 7) % $clientes->count());
            $subtotalPos = 15.00 + ($pos * 4.50);
            $ivaPos = round($subtotalPos * 0.15, 2);
            $propinaPos = round($subtotalPos * 0.10, 2);
            $totalPos = round($subtotalPos + $ivaPos + $propinaPos, 2);
            $fechaPos = Carbon::today()->subDays($pos * 2)->setTime(12 + ($pos % 8), 15);

            $numeroVenta = 'VNT-POS-'.str_pad((string) $pos, 5, '0', STR_PAD_LEFT);
            $venta = Venta::query()->updateOrCreate(
                ['numero_venta' => $numeroVenta],
                [
                    'cliente_id' => $cliente?->id,
                    'moneda_id' => $monedaUsd->id,
                    'subtotal' => $subtotalPos,
                    'descuento_total' => 0.00,
                    'impuesto_total' => $ivaPos,
                    'servicio_total' => 0.00,
                    'propina_total' => $propinaPos,
                    'recargo_total' => 0.00,
                    'total' => $totalPos,
                    'estado' => EstadoVenta::Emitida,
                    'datos_fiscales' => [
                        'tipo' => 'pos_mostrador',
                        'terminal' => 'CAJA-POS-01',
                    ],
                    'creada_por' => $adminId,
                    'created_at' => $fechaPos,
                ]
            );

            VentaDetalle::query()->updateOrCreate(
                ['venta_id' => $venta->id, 'concepto' => "Consumo Directo Mostrador / Cafetería POS #{$pos}"],
                ['cantidad' => 1, 'precio_unitario' => $subtotalPos, 'subtotal' => $subtotalPos, 'descuento' => 0.00, 'impuesto' => $ivaPos, 'total_linea' => $subtotalPos]
            );
        }

        $this->command->info('Facturas en borrador, facturas anuladas y ventas directas POS sembradas con éxito.');
    }
}
