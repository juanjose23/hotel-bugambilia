@extends('layouts.reporte-htb')

@section('report_code', 'HTB-INV-004')
@section('report_name', 'Lotes en Cuarentena')

@section('content')
@php
    $kpiCuarentena = $paginas[0] ?? collect();
    $totalUnidades = collect($paginas)->flatten(1)->sum('cantidad_disponible');
    $totalCriticos = collect($paginas)->flatten(1)->filter(fn($l) => now()->diffInDays($l->updated_at) > 7)->count();
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
                                    <div class="hdr-title">Lotes en Cuarentena</div>
                                    <div class="hdr-code">Documento: HTB-INV-004</div>
                                    <div class="hdr-sub">Sistema de Gestión Operativa · Hotel Bugambilias</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- KPIs solo en primera página --}}
                    @if($loop->first)
                    <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Lotes en Cuarentena</div>
                                <div style="font-size: 15px; font-weight: 800; color: #711C37;">{{ $totalRegistros }}</div>
                            </td>
                            <td style="width: 12px;"></td>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Unidades Retenidas</div>
                                <div style="font-size: 15px; font-weight: 800; color: #711C37;">{{ number_format($totalUnidades, 2) }}</div>
                            </td>
                            <td style="width: 12px;"></td>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">&gt; 7 días retenidos</div>
                                <div style="font-size: 15px; font-weight: 800; color: #dc2626;">{{ $totalCriticos }}</div>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código Lote</th>
                                <th>Producto</th>
                                <th style="text-align:right;">Cantidad</th>
                                <th>Ubicación</th>
                                <th style="text-align:center;">Días en Cuarentena</th>
                                <th>Fecha Vence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $lote)
                            @php $dias = now()->diffInDays($lote->updated_at); @endphp
                            <tr>
                                <td style="font-family:monospace;font-size:9px;">{{ $lote->codigo_lote }}</td>
                                <td>{{ $lote->producto?->nombre }}</td>
                                <td style="text-align:right;">{{ number_format((float)$lote->cantidad_disponible, 2) }}</td>
                                <td>{{ $lote->ubicacion?->nombre }}</td>
                                <td style="text-align:center;{{ $dias > 7 ? 'color:#dc2626;font-weight:bold;' : 'color:#d97706;' }}">{{ $dias }} días</td>
                                <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin lotes en cuarentena.</td></tr>
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
