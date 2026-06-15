@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-008')
@section('report_name', 'Garantías Próximas a Vencer')

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
                                    <div class="hdr-title" style="font-size: 14px;">Garantías Próximas a Vencer</div>
                                    <div class="hdr-code">HTB-ACT-008</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        &nbsp;|&nbsp; <strong>Días de anticipación:</strong> {{ $dias }}
                        &nbsp;|&nbsp; <strong>Total:</strong> {{ $totalRegistros }}
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Activo</th>
                                <th>Proveedor</th>
                                <th>Fin Garantía</th>
                                <th style="text-align:center;">Días Rest.</th>
                                <th style="text-align:right;">Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $activo)
                            @php
                                $diasRestantes = now()->diffInDays($activo->fecha_garantia_fin, false);
                                $vencida  = $diasRestantes < 0;
                                $critica  = $diasRestantes <= 30 && $diasRestantes >= 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $activo->codigo_inventario }}</strong></td>
                                <td>
                                    {{ $activo->nombre_descriptivo }}
                                    <br><small style="color:#999;">{{ $activo->producto?->nombre }}</small>
                                </td>
                                <td>{{ $activo->proveedor?->persona?->nombre_completo ?? '—' }}</td>
                                <td>{{ $activo->fecha_garantia_fin->format('d/m/Y') }}</td>
                                <td style="text-align:center;font-weight:bold;color:{{ $vencida ? '#dc2626' : ($critica ? '#f59e0b' : '#16a34a') }};">
                                    {{ $vencida ? 'VENCIDA' : $diasRestantes . ' días' }}
                                </td>
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
                                <td colspan="6" style="text-align:center;color:#999;font-style:italic;">No hay activos con garantías próximas a vencer.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($loop->last)
                    <div style="margin-top:16px;font-size:9px;color:#666;">
                        <span style="color:#dc2626;font-weight:bold;">■</span> Vencida &nbsp;&nbsp;
                        <span style="color:#f59e0b;font-weight:bold;">■</span> Próxima a vencer (&le;30 días) &nbsp;&nbsp;
                        <span style="color:#16a34a;font-weight:bold;">■</span> Vigente
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte de garantías para reclamaciones oportunas.</td>
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
