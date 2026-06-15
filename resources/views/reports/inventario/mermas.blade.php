@extends('layouts.reporte-htb')

@section('report_code', 'HTB-INV-006')
@section('report_name', 'Mermas / Lotes Dados de Baja')

@section('content')
@php
    $allLotes      = collect($paginas)->flatten(1);
    $totalVencidos = $allLotes->filter(fn($l) => $l->estado?->value === 3)->count();
    $totalRechazados = $allLotes->filter(fn($l) => $l->estado?->value === 4)->count();
    $desde = $filtros['periodo_desde'] ?? '—';
    $hasta = $filtros['periodo_hasta'] ?? '—';
    $filtrosTexto = "Período: {$desde} a {$hasta}" . (!empty($filtros['motivo']) ? " | Motivo: {$filtros['motivo']}" : '');
@endphp
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
                                        <strong style="font-size:17px;color:#711C37;">HOTEL BUGAMBILIAS</strong>
                                    @endif
                                </td>
                                <td style="text-align:right; padding-right:2px;">
                                    <div class="hdr-title">Mermas / Lotes Dados de Baja</div>
                                    <div class="hdr-code">Documento: HTB-INV-006</div>
                                    <div class="hdr-sub">Sistema de Gestión Operativa · Hotel Bugambilias</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    @if($loop->first)
                    <div style="margin-bottom:10px;background:#f8fafc;padding:8px 10px;border:1px solid #e2e8f0;font-size:9px;">
                        <span style="color:#711C37;font-weight:bold;text-transform:uppercase;">Filtros: </span>
                        <span>{{ $filtrosTexto }}</span>
                    </div>
                    <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Lotes con Merma</div>
                                <div style="font-size: 15px; font-weight: 800; color: #711C37;">{{ $totalRegistros }}</div>
                            </td>
                            <td style="width: 12px;"></td>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Vencidos</div>
                                <div style="font-size: 15px; font-weight: 800; color: #dc2626;">{{ $totalVencidos }}</div>
                            </td>
                            <td style="width: 12px;"></td>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Rechazados</div>
                                <div style="font-size: 15px; font-weight: 800; color: #f59e0b;">{{ $totalRechazados }}</div>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código Lote</th>
                                <th>Producto</th>
                                <th>Estado</th>
                                <th style="text-align:right;">Cantidad Inicial</th>
                                <th>Fecha Vencimiento</th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $lote)
                            <tr>
                                <td style="font-family:monospace;font-size:9px;">{{ $lote->codigo_lote }}</td>
                                <td>{{ $lote->producto?->nombre }}</td>
                                <td style="color:#dc2626;font-weight:bold;">{{ $lote->estado?->label() }}</td>
                                <td style="text-align:right;">{{ number_format((float)$lote->cantidad_inicial, 2) }}</td>
                                <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $lote->ubicacion?->nombre }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin mermas en el período indicado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table>
                            <tr>
                                <td style="width:36%;">Emisión: {{ $generadoEn }}</td>
                                <td style="width:36%; text-align:center;">Responsable: <strong>{{ $usuario }}</strong></td>
                                <td style="width:28%; text-align:right;">
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
