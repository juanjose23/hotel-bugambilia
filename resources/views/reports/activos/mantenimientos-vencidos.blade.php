@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-012')
@section('report_name', 'Mantenimientos Vencidos')

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
                                    <div class="hdr-title" style="font-size: 14px;">Mantenimientos Vencidos</div>
                                    <div class="hdr-code">HTB-ACT-012</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        &nbsp;|&nbsp; <strong>Total vencidos:</strong> {{ $mantenimientos->count() }}
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Activo</th>
                                <th>Tipo Mtto.</th>
                                <th>Fecha Programada</th>
                                <th style="text-align:center;">Días Vencido</th>
                                <th>Proveedor / Taller</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mantenimientos as $mtto)
                            @php
                                $diasVencido = (int) now()->diffInDays($mtto->fecha_programada, false);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $mtto->activo->codigo_inventario }}</strong>
                                    <br><small style="color:#999;">{{ $mtto->activo->nombre_descriptivo }}</small>
                                </td>
                                <td>{{ $mtto->plan?->tipo ?? '—' }}</td>
                                <td>{{ $mtto->fecha_programada->format('d/m/Y') }}</td>
                                <td style="text-align:center;font-weight:bold;color:{{ $diasVencido > 30 ? '#dc2626' : '#f59e0b' }};">
                                    {{ $diasVencido }} días
                                </td>
                                <td>{{ $mtto->plan?->proveedor?->persona?->nombre_completo ?? '—' }}</td>
                                <td style="text-align:center;"><span class="badge">{{ $mtto->estado?->label() }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align:center;color:#999;font-style:italic;">No hay mantenimientos vencidos o programados pendientes.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div style="margin-top:16px;font-size:9px;color:#666;">
                        <span style="color:#dc2626;font-weight:bold;">■</span> Crítico (&gt;30 días vencido) &nbsp;&nbsp;
                        <span style="color:#f59e0b;font-weight:bold;">■</span> Pendiente (&le;30 días)
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte de mantenimientos programados no ejecutados.</td>
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
