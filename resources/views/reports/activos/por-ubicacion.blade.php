@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-005')
@section('report_name', 'Activos por Ubicación')

@section('content')
@foreach($ubicaciones as $ubicacion)
<div class="report-page @if(!$loop->last) page-break @endif">
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
                                    <div class="hdr-title" style="font-size: 14px;">Activos por Ubicación</div>
                                    <div class="hdr-code">HTB-ACT-005</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                    </div>

                    <div class="pcard">
                        <div class="pcard-hdr">
                            <table>
                                <tr>
                                    <td>
                                        <div class="pcard-title">{{ $ubicacion['tipo'] }}: {{ $ubicacion['nombre'] }}</div>
                                        <div class="pcard-meta">Total activos: <strong>{{ count($ubicacion['activos']) }}</strong></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Activo</th>
                                    <th>Nro. Serie</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ubicacion['activos'] as $activo)
                                <tr>
                                    <td><strong>{{ $activo->codigo_inventario }}</strong></td>
                                    <td>{{ $activo->nombre_descriptivo }}<br><small style="color:#999;">{{ $activo->producto?->nombre }}</small></td>
                                    <td><code style="font-family:monospace;">{{ $activo->numero_serie ?: '—' }}</code></td>
                                    <td style="text-align:center;"><span class="badge">{{ $activo->estado?->label() }}</span></td>
                                    <td style="text-align:right;font-weight:bold;">
                                        @if($activo->costo_adquisicion !== null)
                                            {{ $activo->moneda?->simbolo ?? '$' }}{{ number_format($activo->costo_adquisicion, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#999;font-style:italic;">Sin activos asignados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($ubicacion['subtotal']))
                    <div style="text-align:right;margin-top:8px;font-size:10px;">
                        <strong>Subtotal ubicación:</strong>
                        {{ $ubicacion['moneda'] }}{{ number_format($ubicacion['subtotal'], 2) }}
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Activos agrupados por ubicación actual.</td>
                                <td style="text-align:right;font-weight:bold;color:#711C37;text-transform:uppercase;">Sistema de Gestión de Activos</td>
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
