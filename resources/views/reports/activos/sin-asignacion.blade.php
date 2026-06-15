@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-011')
@section('report_name', 'Activos Sin Asignación')

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
                                    <div class="hdr-title" style="font-size: 14px;">Activos Sin Asignación</div>
                                    <div class="hdr-code">HTB-ACT-011</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        &nbsp;|&nbsp; <strong>Total sin asignar:</strong> {{ $totalRegistros }}
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Activo</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Fecha Adq.</th>
                                <th>Proveedor</th>
                                <th style="text-align:right;">Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $activo)
                            <tr>
                                <td><strong>{{ $activo->codigo_inventario }}</strong></td>
                                <td>
                                    {{ $activo->nombre_descriptivo }}
                                    <br><small style="color:#999;">{{ $activo->numero_serie ? ('SN: '.$activo->numero_serie) : '' }}</small>
                                </td>
                                <td>{{ $activo->producto?->nombre ?? '—' }}</td>
                                <td style="text-align:center;"><span class="badge">{{ $activo->estado?->label() }}</span></td>
                                <td>{{ $activo->fecha_adquisicion->format('d/m/Y') }}</td>
                                <td>{{ $activo->proveedor?->persona?->nombre_completo ?? '—' }}</td>
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
                                <td colspan="7" style="text-align:center;color:#999;font-style:italic;">Todos los activos tienen asignación actual.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($loop->last)
                    <div style="margin-top:16px;font-size:9px;color:#666;">
                        Estos activos no tienen una asignación vigente (sin ubicación física conocida). Se recomienda regularizar su situación.
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte de activos sin ubicación física actual.</td>
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
