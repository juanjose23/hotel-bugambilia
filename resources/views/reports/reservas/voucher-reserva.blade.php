@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('extra-css')
    .report-footer { position: fixed; bottom: -3mm; left: 4mm; right: 4mm; }
    .report-content { padding-bottom: 14mm; }
    .voucher-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 9px; background: #fff; }
    .voucher-label { color: #711C37; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
    .voucher-value { margin-top: 2px; color: #172033; font-size: 11px; font-weight: bold; }
    .voucher-muted { color: #64748b; font-size: 8px; }
    .financial-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .financial-table th { background: #711C37; color: #fff; padding: 7px; border: 1px solid #5a1530; font-size: 8px; text-transform: uppercase; }
    .financial-table td { padding: 7px; border: 1px solid #e2e8f0; font-size: 9px; }
    .financial-table tfoot td { background: #f8fafc; font-weight: bold; }
    .financial-table .grand-total td { background: #711C37; color: #fff; font-size: 11px; }
@endsection

@section('content')
    @php
        $simbolo = $reserva->moneda?->simbolo ?? 'C$';
        $esRestaurante = $reserva->tipo_reserva === \App\Enums\Reservas\TipoReserva::RESTAURANTE;
        $resumenRestaurante = is_array($reserva->meta_datos['resumen_restaurante'] ?? null)
            ? $reserva->meta_datos['resumen_restaurante']
            : [];
        $costoMesa = $esRestaurante
            ? (float) ($resumenRestaurante['costo_mesas'] ?? 0)
            : (float) $reserva->subtotal;
        $costoPreorden = $esRestaurante
            ? (float) ($resumenRestaurante['costo_preorden'] ?? collect($detallePreorden)->sum('subtotal'))
            : 0.0;
        $subtotalConceptos = $costoMesa + $costoPreorden;
        $otrosCargos = max(0, (float) $reserva->total - $subtotalConceptos + (float) $reserva->descuento);
        $cantidadPreorden = collect($detallePreorden)->sum('cantidad');
        $pagos = $reserva->cuentas->flatMap->pagos;
    @endphp

    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>

        <div class="report-content" style="margin-top: 9px;">
            {{-- Identificación superior y código de barras --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid;">
                <tr>
                    <td style="width: 48%; vertical-align: middle; padding: 0; border: 0;">
                        <div class="voucher-label">Comprobante de reserva</div>
                        <div style="font-size: 17px; font-weight: bold; color: #711C37; margin-top: 2px;">{{ $reserva->codigo_reserva }}</div>
                        <div class="voucher-muted" style="margin-top: 3px;">Emitido: {{ $fechaEmision }} · {{ $estadoLabel }}</div>
                    </td>
                    <td style="width: 52%; vertical-align: middle; text-align: right; padding: 0; border: 0;">
                        @if (!empty($barcodeBase64))
                            <img src="{{ $barcodeBase64 }}" style="width: 250px; height: 48px; display: inline-block;" alt="Código de barras de la reserva">
                            <div style="width: 250px; margin-left: auto; text-align: center; font-family: monospace; font-size: 8px; letter-spacing: 1px;">{{ $reserva->codigo_reserva }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Datos esenciales --}}
            <table style="width: 100%; border-collapse: separate; border-spacing: 5px; margin: 0 -5px 8px; page-break-inside: avoid;">
                <tr>
                    <td class="voucher-card" style="width: 38%; vertical-align: top;">
                        <div class="voucher-label">Cliente / titular</div>
                        <div class="voucher-value">{{ $reserva->nombre_cliente }}</div>
                        <div class="voucher-muted">{{ $reserva->telefono_cliente ?? 'Sin teléfono' }}</div>
                        <div class="voucher-muted">{{ $reserva->email_cliente ?? 'Sin correo' }}</div>
                    </td>
                    <td class="voucher-card" style="width: 32%; vertical-align: top;">
                        <div class="voucher-label">Fecha y horario</div>
                        <div class="voucher-value">{{ $reserva->fecha_check_in->format('d/m/Y') }}</div>
                        @if ($esRestaurante)
                            <div class="voucher-muted">{{ $reserva->hora_reserva ?? 'Hora pendiente' }} · {{ $resumenRestaurante['horas'] ?? 1 }} hora(s)</div>
                        @else
                            <div class="voucher-muted">Salida: {{ $reserva->fecha_check_out?->format('d/m/Y') ?? 'N/D' }}</div>
                        @endif
                    </td>
                    <td class="voucher-card" style="width: 30%; vertical-align: top;">
                        <div class="voucher-label">Recurso reservado</div>
                        <div class="voucher-value">{{ $reserva->habitacion?->nombre ?? $reserva->espacio?->nombre ?? 'Por asignar' }}</div>
                        <div class="voucher-muted">{{ $reserva->adultos }} persona(s)</div>
                    </td>
                </tr>
            </table>

            {{-- Única tabla consolidada --}}
            <table class="financial-table" style="page-break-inside: avoid;">
                <thead>
                    <tr>
                        <th style="text-align: left;">Concepto</th>
                        <th style="width: 65px; text-align: center;">Cantidad</th>
                        <th style="width: 110px; text-align: right;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $esRestaurante ? 'Reserva de mesa' : 'Reserva de recurso' }}</strong>
                            <div class="voucher-muted">
                                {{ $reserva->habitacion?->nombre ?? $reserva->espacio?->nombre ?? $reserva->tipo_reserva->getLabel() }}
                                @if ($esRestaurante) · {{ $resumenRestaurante['horas'] ?? 1 }} hora(s) @endif
                            </div>
                        </td>
                        <td style="text-align: center;">{{ $esRestaurante ? ($resumenRestaurante['mesas_seleccionadas'] ?? 1) : 1 }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $simbolo }} {{ number_format($costoMesa, 2) }}</td>
                    </tr>
                    @if (! empty($detallePreorden))
                        @foreach ($detallePreorden as $itemPre)
                            <tr>
                                <td style="padding-left: 12px;">
                                    <strong>• {{ $itemPre['nombre'] ?? 'Platillo preordenado' }}</strong>
                                    @if (! empty($itemPre['observaciones']))
                                        <div class="voucher-muted">Nota: {{ $itemPre['observaciones'] }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $itemPre['cantidad'] ?? 1 }}</td>
                                <td style="text-align: right;">{{ $simbolo }} {{ number_format((float) ($itemPre['subtotal'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" style="text-align: right;">Subtotal de conceptos</td>
                        <td style="text-align: right;">{{ $simbolo }} {{ number_format($subtotalConceptos, 2) }}</td>
                    </tr>
                    @if ((float) $reserva->descuento > 0)
                        <tr>
                            <td colspan="2" style="text-align: right;">Descuento</td>
                            <td style="text-align: right;">- {{ $simbolo }} {{ number_format((float) $reserva->descuento, 2) }}</td>
                        </tr>
                    @endif
                    @if ($otrosCargos > 0)
                        <tr>
                            <td colspan="2" style="text-align: right;">Impuestos y cargos</td>
                            <td style="text-align: right;">{{ $simbolo }} {{ number_format($otrosCargos, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td colspan="2" style="text-align: right;">Total de la reserva</td>
                        <td style="text-align: right;">{{ $simbolo }} {{ number_format((float) $reserva->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Estado de pago y verificación --}}
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: avoid;">
                <tr>
                    <td style="width: 68%; vertical-align: top; padding-right: 12px; border: 0;">
                        <div class="voucher-card">
                            <div class="voucher-label">Estado del pago</div>
                            <div style="margin-top: 5px; font-size: 10px;">
                                Pagado: <strong>{{ $simbolo }} {{ number_format((float) $reserva->total_pagado, 2) }}</strong>
                                &nbsp; · &nbsp;
                                Saldo: <strong style="color: #711C37;">{{ $simbolo }} {{ number_format((float) $reserva->saldo, 2) }}</strong>
                            </div>
                            <div class="voucher-muted" style="margin-top: 4px;">
                                {{ $pagos->isEmpty() ? 'No hay pagos registrados.' : $pagos->count().' pago(s) o abono(s) registrado(s).' }}
                            </div>
                        </div>
                        <div style="margin-top: 8px; border-left: 3px solid #711C37; padding: 6px 8px; background: #f8fafc; color: #475569; font-size: 8px; line-height: 1.45;">
                            Presente este comprobante al llegar. El código QR y el código de barras identifican la reserva en recepción o restaurante.
                        </div>
                    </td>
                    <td style="width: 32%; text-align: center; vertical-align: top; border: 0;">
                        @if (!empty($qrCodeBase64))
                            <img src="{{ $qrCodeBase64 }}" style="width: 88px; height: 88px; display: block; margin: 0 auto 3px;" alt="Código QR de verificación">
                            <div class="voucher-label">Verificación QR</div>
                            <div class="voucher-muted" style="font-family: monospace;">{{ substr($tokenQr, 0, 16) }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @include('reports.layout.partials.footer', [
            'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            'usuario' => auth()->user()?->name ?? 'Sistema',
            'generadoEn' => $fechaEmision,
        ])
    </div>
@endsection
