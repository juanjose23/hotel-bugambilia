@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-001')
@section('report_name', 'Inventario General de Activos Fijos')

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
                                    <div class="hdr-title" style="font-size: 14px;">Inventario General de Activos Fijos</div>
                                    <div class="hdr-code">HTB-ACT-001</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Meta info de la exportación -->
                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                    </div>

                    <!-- Tabla de Datos -->
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre / Descripción</th>
                                <th>Tipo de Activo</th>
                                <th>Nro. Serie</th>
                                <th>Ubicación Actual</th>
                                <th style="text-align: right;">Costo</th>
                                <th style="text-align: center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activos as $activo)
                            <tr>
                                <td><strong>{{ $activo->codigo_inventario }}</strong></td>
                                <td>{{ $activo->nombre_descriptivo }}</td>
                                <td>{{ $activo->producto?->nombre ?? 'N/A' }}</td>
                                <td><code style="font-family: monospace;">{{ $activo->numero_serie ?: '—' }}</code></td>
                                <td>
                                    @if($activo->asignacionActiva?->asignable)
                                        @php
                                            $tipo = class_basename($activo->asignacionActiva->asignable_type);
                                            $prefijo = match ($tipo) {
                                                'Habitacion' => 'Hab.',
                                                'Ubicacion' => 'Ubic.',
                                                'Espacio' => 'Esp.',
                                                default => $tipo,
                                            };
                                        @endphp
                                        {{ $prefijo }} {{ $activo->asignacionActiva->asignable->nombre }}
                                    @else
                                        <span style="color: #999; font-style: italic;">Sin asignar</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: bold;">
                                    @if($activo->costo_adquisicion !== null)
                                        {{ $activo->moneda?->simbolo ?? '$' }}{{ number_format($activo->costo_adquisicion, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="font-size: 8px;">{{ $activo->estado?->label() ?? 'N/A' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: #999; font-style: italic;">No hay activos registrados en el inventario.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 8px; color: #999;">Este reporte es una representación fidedigna de los activos físicos de Hotel Bugambilias.</td>
                                <td style="text-align: right; font-weight: bold; color: #711C37; text-transform: uppercase;">Sistema de Gestión de Activos</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
