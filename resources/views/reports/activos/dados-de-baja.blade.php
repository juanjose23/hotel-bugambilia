@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-009')
@section('report_name', 'Activos Dados de Baja')

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
                                    <div class="hdr-title" style="font-size: 14px;">Activos Dados de Baja</div>
                                    <div class="hdr-code">HTB-ACT-009</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                        &nbsp;|&nbsp; <strong>Total bajas:</strong> {{ $totalRegistros }}
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Folio Baja</th>
                                <th>Código Activo</th>
                                <th>Activo</th>
                                <th>Motivo</th>
                                <th>Fecha Baja</th>
                                <th style="text-align:right;">Valor Residual</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $baja)
                            <tr>
                                <td><strong>{{ $baja->codigo }}</strong></td>
                                <td>{{ $baja->activo->codigo_inventario }}</td>
                                <td>{{ $baja->activo->nombre_descriptivo }}</td>
                                <td>
                                    <span class="badge" style="
                                        background: {{ match($baja->motivo_tipo?->value) {
                                            'robo', 'perdida'      => '#fee2e2',
                                            'obsolescencia'        => '#f1f5f9',
                                            'daño_irreparable'     => '#fef3c7',
                                            'donacion'             => '#d1fae5',
                                            'venta'                => '#dbeafe',
                                            default                => '#f8fafc',
                                        } }};
                                        color: {{ match($baja->motivo_tipo?->value) {
                                            'robo', 'perdida'      => '#991b1b',
                                            'obsolescencia'        => '#475569',
                                            'daño_irreparable'     => '#92400e',
                                            'donacion'             => '#065f46',
                                            'venta'                => '#1e40af',
                                            default                => '#333',
                                        } }};
                                    ">{{ $baja->motivo_tipo?->label() ?? $baja->motivo_tipo }}</span>
                                </td>
                                <td>{{ $baja->fecha_baja->format('d/m/Y') }}</td>
                                <td style="text-align:right;font-weight:bold;">
                                    @if($baja->valor_residual !== null)
                                        ${{ number_format($baja->valor_residual, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $baja->creadoPor?->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" style="text-align:center;color:#999;font-style:italic;">No hay activos dados de baja registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($loop->last)
                    <div style="margin-top:16px;font-size:9px;color:#666;">
                        &bull; <strong>Valor residual total:</strong> ${{ number_format($totalValorResidual, 2) }}
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Reporte patrimonial de activos retirados definitivamente.</td>
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
