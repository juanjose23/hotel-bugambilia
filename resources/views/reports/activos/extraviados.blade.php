@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-010')
@section('report_name', 'Activos Extraviados')

@section('content')
@foreach ($paginas as $pIdx => $chunk)
<div class="report-page {{ $loop->last ? '' : 'page-break' }}">
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
                                    <div class="hdr-title" style="font-size: 14px;">Activos Extraviados</div>
                                    <div class="hdr-code">HTB-ACT-010</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        &nbsp;|&nbsp; <strong>Total extraviados:</strong> {{ $totalRegistros }}
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Activo</th>
                                <th>Última Ubicación Conocida</th>
                                <th>Fecha Adq.</th>
                                <th style="text-align:right;">Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $activo)
                            <tr>
                                <td><strong>{{ $activo->codigo_inventario }}</strong></td>
                                <td>
                                    {{ $activo->nombre_descriptivo }}
                                    <br><small style="color:#999;">{{ $activo->producto?->nombre }}</small>
                                </td>
                                <td>
                                    @php
                                        $ultAsignacion = $activo->asignaciones->sortByDesc('fecha_inicio')->first();
                                    @endphp
                                    @if($ultAsignacion?->asignable)
                                        {{ class_basename($ultAsignacion->asignable_type) }}: {{ $ultAsignacion->asignable->nombre }}
                                        <br><small style="color:#999;">{{ $ultAsignacion->fecha_inicio->format('d/m/Y') }}</small>
                                    @else
                                        <span style="color:#999;font-style:italic;">Desconocida</span>
                                    @endif
                                </td>
                                <td>{{ $activo->fecha_adquisicion->format('d/m/Y') }}</td>
                                <td style="text-align:right;font-weight:bold;color:#dc2626;">
                                    @if($activo->costo_adquisicion !== null)
                                        {{ $activo->moneda?->simbolo ?? '$' }}{{ number_format($activo->costo_adquisicion, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:#999;font-style:italic;">No hay activos reportados como extraviados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($loop->last)
                    <div style="margin-top:16px;font-size:10px;font-weight:bold;color:#dc2626;">
                        Pérdida total estimada: ${{ number_format($totalCosto, 2) }}
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte de activos con estado Extraviado para investigación y recuperación.</td>
                                <td style="text-align:center;font-weight:bold;color:#711C37;text-transform:uppercase;">Sistema de Gestión de Activos</td>
                                <td style="text-align:right;width:120px;font-size:9px;color:#718096;">
                                    Página <strong>{{ $pIdx + 1 }}</strong> de <strong>{{ count($paginas) }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endforeach
@endsection
