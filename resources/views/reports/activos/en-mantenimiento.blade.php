@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-007')
@section('report_name', 'Activos en Mantenimiento')

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
                                    <div class="hdr-title" style="font-size: 14px;">Activos en Mantenimiento</div>
                                    <div class="hdr-code">HTB-ACT-007</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        &nbsp;|&nbsp; <strong>Total en taller:</strong> {{ $activos->count() }}
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Activo</th>
                                <th>Taller / Ubicación</th>
                                <th>Desde</th>
                                <th style="text-align:center;">Días</th>
                                <th>Tipo Mtto.</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activos as $item)
                            @php
                                $mttoActual = $item->mantenimientos->first();
                            @endphp
                            <tr>
                                <td><strong>{{ $item->codigo_inventario }}</strong></td>
                                <td>
                                    {{ $item->nombre_descriptivo }}
                                    <br><small style="color:#999;">{{ $item->producto?->nombre }}</small>
                                </td>
                                <td>
                                    @if($mttoActual?->plan?->proveedor)
                                        {{ $mttoActual->plan->proveedor->persona?->nombre_completo ?? 'Taller interno' }}
                                    @else
                                        <span style="color:#999;font-style:italic;">Taller interno</span>
                                    @endif
                                </td>
                                <td>{{ $mttoActual?->fecha_programada?->format('d/m/Y') ?? '—' }}</td>
                                <td style="text-align:center;font-weight:bold;">
                                    {{ $mttoActual ? (int) $mttoActual->fecha_programada->diffInDays(now()) : '—' }}
                                </td>
                                <td>{{ $mttoActual?->plan?->tipo ?? '—' }}</td>
                                <td style="text-align:center;">
                                    <span class="badge">{{ $mttoActual?->estado?->label() ?? $item->estado?->label() }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" style="text-align:center;color:#999;font-style:italic;">No hay activos en mantenimiento actualmente.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div style="margin-top:16px;font-size:9px;color:#666;">
                        Este reporte muestra los activos cuyo estado es <strong>En mantenimiento</strong>, incluyendo el taller asignado y el tiempo transcurrido desde su ingreso.
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte operacional de activos fuera de servicio por mantenimiento.</td>
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
