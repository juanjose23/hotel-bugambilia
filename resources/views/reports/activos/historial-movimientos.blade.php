@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-006')
@section('report_name', 'Historial de Movimientos de Activo')

@section('content')
<div class="report-page">
    <table class="page-frame">
        <tbody>
            <tr>
                <td class="frame-body" style="padding: 40px;">
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
                                    <div class="hdr-title" style="font-size: 14px;">Historial de Movimientos</div>
                                    <div class="hdr-code">HTB-ACT-006</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        @if($filtroActivo)
                            &nbsp;|&nbsp; <strong>Activo:</strong> {{ $filtroActivo->codigo_inventario }} - {{ $filtroActivo->nombre_descriptivo }}
                        @endif
                    </div>

                    <div class="pcard">
                        <div class="pcard-hdr">
                            <table>
                                <tr>
                                    <td>
                                        <div class="pcard-title">Línea de Tiempo del Activo</div>
                                        <div class="pcard-meta">
                                            {{ $activo->codigo_inventario }} — {{ $activo->nombre_descriptivo }}
                                            @if($activo->producto)
                                                | {{ $activo->producto->nombre }}
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:right;">
                                        <span class="badge">{{ $activo->estado?->label() }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:80px;">Fecha</th>
                                    <th style="width:120px;">Tipo de Evento</th>
                                    <th>Detalle</th>
                                    <th style="width:100px;">Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lineaTiempo as $evento)
                                <tr>
                                    <td>{{ $evento['fecha'] }}</td>
                                    <td>
                                        <span class="badge" style="
                                            background: {{ $evento['color'] }}20;
                                            color: {{ $evento['color'] }};
                                            border: 1px solid {{ $evento['color'] }}40;
                                        ">{{ $evento['tipo'] }}</span>
                                    </td>
                                    <td>{{ $evento['detalle'] }}</td>
                                    <td>{{ $evento['responsable'] ?: '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#999;font-style:italic;">No hay movimientos registrados para este activo.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:20px;font-size:9px;color:#666;">
                        <strong>Nota:</strong> Se muestran todas las asignaciones, mantenimientos y bajas registradas para este activo en orden cronológico.
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte de trazabilidad individual del activo.</td>
                                <td style="text-align:right;font-weight:bold;color:#711C37;text-transform:uppercase;">Sistema de Gestión de Activos</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
