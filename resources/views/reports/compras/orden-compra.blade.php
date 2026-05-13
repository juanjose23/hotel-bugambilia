@extends('layouts.reporte-htb')

@section('report_code', 'HTB-COM-003')
@section('report_name', 'Orden de Compra')

@section('content')
<div class="report-page">
    <table class="page-frame">
        <tbody>
            <tr>
                <td class="frame-body" style="padding: 40px;">
                    <!-- Encabezado -->
                    <div class="doc-header">
                        <table>
                            <tr>
                                <td style="width:35%;">
                                    @if(!empty($logo_base64))
                                        <img src="{{ $logo_base64 }}" class="hdr-logo">
                                    @else
                                        <div class="hdr-title">Hotel Bugambilias</div>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <div class="hdr-title" style="font-size: 14px;">Orden de Compra</div>
                                    <div class="hdr-code">HTB-COM-003</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Información General -->
                    <div style="margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 50%; vertical-align: top;">
                                    <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Proveedor</strong><br>
                                    <span style="font-size: 11px; font-weight: bold;">{{ $record->proveedor->persona->personaJuridica->razon_social ?? $record->proveedor->persona->nombre_completo }}</span><br>
                                    <span style="color: #666;">RUC/ID: {{ $record->proveedor->persona->personaJuridica->ruc ?? '—' }}</span>
                                </td>
                                <td style="width: 50%; vertical-align: top; text-align: right;">
                                    <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Detalles de Orden</strong><br>
                                    <span style="font-size: 11px; font-weight: bold;">Emisión: {{ $record->fecha_orden->format('d/m/Y') }}</span><br>
                                    <span class="badge badge-on">{{ $record->estado?->label() ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Tabla de Items -->
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Descripción del Producto</th>
                                <th style="text-align: center;">Cant.</th>
                                <th style="text-align: center;">P. Unit.</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->producto->nombre }}</strong>
                                    @if($item->variante)
                                        <br><span style="font-size: 8px; color: #666;">Variante: {{ $item->variante->codigo }}</span>
                                    @endif
                                    <br><small style="color: #999; text-transform: uppercase;">{{ $item->unidadMedida->nombre ?? 'Unidad' }}</small>
                                </td>
                                <td style="text-align: center;">{{ number_format($item->cantidad, 2) }}</td>
                                <td style="text-align: center;">${{ number_format($item->precio_unitario, 2) }}</td>
                                <td style="text-align: right; font-weight: bold;">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totales -->
                    <div style="margin-top: 20px; text-align: right;">
                        <table style="width: 250px; margin-left: auto; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 5px; color: #666;">Subtotal:</td>
                                <td style="padding: 5px; text-align: right;">${{ number_format($record->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; color: #666;">Impuestos:</td>
                                <td style="padding: 5px; text-align: right;">${{ number_format($record->impuestos, 2) }}</td>
                            </tr>
                            <tr style="border-top: 2px solid #711C37;">
                                <td style="padding: 10px 5px; font-weight: bold; color: #711C37; font-size: 14px;">TOTAL:</td>
                                <td style="padding: 10px 5px; text-align: right; font-weight: bold; color: #711C37; font-size: 16px;">${{ number_format($record->total, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Cláusulas -->
                    <div style="margin-top: 30px; background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; font-size: 8px; color: #666; border-radius: 4px;">
                        <strong style="display: block; margin-bottom: 5px; color: #711C37; text-transform: uppercase;">Términos y Condiciones:</strong>
                        1. La mercancía debe entregarse acompañada de esta orden y factura.<br>
                        2. El hotel se reserva el derecho de rechazo por calidad.<br>
                        3. Pago según condición acordada: <strong>{{ $record->condicionPago->nombre }}</strong>.
                    </div>

                    <!-- Firmas -->
                    <div style="margin-top: 40px;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                                    <strong style="font-size: 8px; text-transform: uppercase; color: #999;">Autorizado por (Compras)</strong>
                                </td>
                                <td style="width: 10%;"></td>
                                <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                                    <strong style="font-size: 8px; text-transform: uppercase; color: #999;">Recibido por (Proveedor)</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 8px; color: #999;">Representación física de un registro digital - ERP Hotel Bugambilias.</td>
                                <td style="text-align: right; font-weight: bold; color: #711C37; text-transform: uppercase;">Sistema de Gestión de Compras</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
