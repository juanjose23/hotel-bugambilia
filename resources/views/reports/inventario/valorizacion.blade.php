@extends('layouts.reporte-htb')

@section('report_code', 'HTB-INV-007')
@section('report_name', 'Valorización del Inventario')

@section('content')
@php
    $allFilas        = collect($paginas)->flatten(1);
    $categoriaCount  = $allFilas->unique('categoria')->count();
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
                                    <div class="hdr-title">Valorización del Inventario</div>
                                    <div class="hdr-code">Documento: HTB-INV-007</div>
                                    <div class="hdr-sub">Sistema de Gestión Operativa · Hotel Bugambilias</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    @if($loop->first)
                    <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Total Valorizado</div>
                                <div style="font-size: 15px; font-weight: 800; color: #16a34a;">${{ number_format($totalGeneral, 2) }}</div>
                            </td>
                            <td style="width: 12px;"></td>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Categorías Diferentes</div>
                                <div style="font-size: 15px; font-weight: 800; color: #711C37;">{{ $categoriaCount }}</div>
                            </td>
                            <td style="width: 12px;"></td>
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Productos Valorizados</div>
                                <div style="font-size: 15px; font-weight: 800; color: #711C37;">{{ $totalRegistros }}</div>
                            </td>
                        </tr>
                    </table>
                    @endif

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Ubicación</th>
                                <th style="text-align:right;">Stock Total</th>
                                <th style="text-align:right;">Costo Prom.</th>
                                <th style="text-align:right;">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chunk as $row)
                            <tr>
                                <td><strong>{{ $row->producto }}</strong></td>
                                <td>{{ $row->categoria ?? '—' }}</td>
                                <td>{{ $row->ubicacion }}</td>
                                <td style="text-align:right;">{{ number_format((float)$row->stock_total, 2) }}</td>
                                <td style="text-align:right;">${{ number_format((float)$row->costo_promedio, 4) }}</td>
                                <td style="text-align:right;color:#16a34a;font-weight:bold;">${{ number_format((float)$row->valor_total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin datos de valorización.</td></tr>
                            @endforelse
                        </tbody>
                        {{-- Total general solo en última página --}}
                        @if($loop->last)
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:10px;">Total General Valorizado:</td>
                                <td style="text-align:right;font-weight:bold;color:#16a34a;font-size:14px;padding:10px;">${{ number_format($totalGeneral, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
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
