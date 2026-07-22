@extends('layouts.reporte-htb')

@section('report_code', 'HTB-COM-001')
@section('report_name', 'Solicitud de Compra')

@section('content')
<div class="report-page">
    <table class="page-frame">
        <tbody>
            <tr>
                <td class="frame-body">
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
                                    <div class="hdr-title" style="font-size: 14px;">Solicitud de Compra</div>
                                    <div class="hdr-code">HTB-COM-001</div>
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
                                    <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Solicitante</strong><br>
                                    <span style="font-size: 11px; font-weight: bold;">{{ $record->colaborador->persona->nombre_completo }}</span><br>
                                    <span style="color: #666;">Dpto: {{ $record->departamentoSolicitante->nombre }}</span>
                                </td>
                                <td style="width: 50%; vertical-align: top; text-align: right;">
                                    <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Estado Actual</strong><br>
                                    <span class="badge badge-on">{{ $record->estado?->label() ?? 'N/A' }}</span><br>
                                    <span style="color: #666;">Fecha Solicitud: {{ $record->fecha_solicitud->format('d/m/Y') }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Justificación -->
                    <div style="background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; margin-bottom: 20px; border-radius: 4px;">
                        <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Justificación de la Compra</strong><br>
                        <p style="font-style: italic; color: #4a5568; margin-top: 5px;">"{{ $record->motivo }}"</p>
                    </div>

                    <!-- Tabla de Items -->
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ítem / Producto</th>
                                <th style="text-align: center;">Cant. Sol.</th>
                                <th style="text-align: center;">Cant. Apr.</th>
                                <th>U.M.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->producto->nombre }}</strong>
                                    @if($item->productoVariante)
                                    <br><small style="color: #999;">{{ $item->productoVariante->codigo }}</small>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ number_format($item->cantidad_solicitada, 2) }}</td>
                                <td style="text-align: center; font-weight: bold; color: #711C37;">{{ $item->cantidad_aprobada ? number_format($item->cantidad_aprobada, 2) : '—' }}</td>
                                <td style="text-transform: uppercase; color: #666;">{{ $item->unidadMedida->nombre ?? 'UNIDAD' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Firmas -->
                    <div style="margin-top: 40px;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                                    <strong style="font-size: 8px; text-transform: uppercase; color: #999;">Firma del Solicitante</strong>
                                </td>
                                <td style="width: 10%;"></td>
                                <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                                    <strong style="font-size: 8px; text-transform: uppercase; color: #999;">Aprobación Administrativa</strong>
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
                                <td style="font-size: 8px; color: #999;">Este documento es una representación física de un registro digital en el sistema ERP Hotel Bugambilias.</td>
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
