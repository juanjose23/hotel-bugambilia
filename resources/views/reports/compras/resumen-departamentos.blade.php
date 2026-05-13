@extends('layouts.reporte-htb')

@section('report_code', 'HTB-COM-005')
@section('report_name', 'Resumen de Compras por Departamento')

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
                                    <div class="hdr-title" style="font-size: 14px;">Resumen por Departamento</div>
                                    <div class="hdr-code">HTB-COM-005</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Generado: {{ now()->format('d/m/Y H:i') }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Filtros / Rango -->
                    <div style="margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 4px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 9px; color: #711C37; font-weight: bold; text-transform: uppercase;">Período Reportado:</span>
                        <span style="font-size: 11px; font-weight: bold; margin-left: 10px;">{{ $fechaInicio ?? 'Histórico' }} — {{ $fechaFin ?? 'Hoy' }}</span>
                    </div>

                    <!-- Tabla de Resumen -->
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th style="text-align: center;">Cant. Órdenes</th>
                                <th style="text-align: right;">Total Ejecutado (OC)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalGral = 0; @endphp
                            @foreach($data as $item)
                            @php $totalGral += $item->total_oc; @endphp
                            <tr>
                                <td><strong>{{ $item->departamento }}</strong></td>
                                <td style="text-align: center;">{{ $item->conteo_ordenes }}</td>
                                <td style="text-align: right; font-weight: bold; color: #711C37;">${{ number_format($item->total_oc, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f1f5f9;">
                                <td colspan="2" style="text-align: right; font-weight: bold; text-transform: uppercase; padding: 10px;">Total General Ejecutado:</td>
                                <td style="text-align: right; font-weight: bold; color: #711C37; font-size: 14px; padding: 10px;">${{ number_format($totalGral, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Gráfico Simple (Barra de Porcentaje) -->
                    <div style="margin-top: 30px;">
                        <strong style="font-size: 9px; color: #711C37; text-transform: uppercase; display: block; margin-bottom: 10px;">Distribución del Gasto</strong>
                        @foreach($data as $item)
                        @php $porcentaje = ($totalGral > 0) ? ($item->total_oc / $totalGral) * 100 : 0; @endphp
                        <div style="margin-bottom: 8px;">
                            <div style="font-size: 8px; margin-bottom: 2px;">{{ $item->departamento }} ({{ number_format($porcentaje, 1) }}%)</div>
                            <div style="width: 100%; background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $porcentaje }}%; background: #711C37; height: 100%;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 8px; color: #999;">Reporte gerencial generado por el Sistema de Compras ERP Hotel Bugambilias.</td>
                                <td style="text-align: right; font-weight: bold; color: #711C37; text-transform: uppercase;">Gestión Administrativa</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
