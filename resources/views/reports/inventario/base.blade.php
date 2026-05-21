@extends('layouts.reporte-htb')

@section('report_code', $codigo ?? 'HTB-INV')
@section('report_name', $titulo ?? 'Reporte de Inventario')

@section('content')
<div class="report-page">
    <table class="page-frame">
        <tbody>
            <tr>
                <td class="frame-body" style="padding:30px;">
                    {{-- Encabezado --}}
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
                                <td style="text-align:right;">
                                    <div class="hdr-title" style="font-size:14px;">{{ $titulo ?? 'Reporte de Inventario' }}</div>
                                    <div class="hdr-code">{{ $codigo ?? 'HTB-INV' }}</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] ?? '' }}</div>
                                    <div class="hdr-sub">Generado: {{ $generadoEn ?? now()->format('d/m/Y H:i') }} | Por: {{ $usuario ?? '' }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- Filtros aplicados --}}
                    @if(!empty($filtrosTexto))
                    <div style="margin-bottom:16px;background:#f8fafc;padding:10px;border:1px solid #e2e8f0;">
                        <span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Filtros Aplicados: </span>
                        <span style="font-size:9px;">{{ $filtrosTexto }}</span>
                    </div>
                    @endif

                    {{-- KPIs opcionales --}}
                    @if(!empty($kpis))
                    <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr>
                            @foreach($kpis as $kpi)
                            <td style="background:#f8fafc; border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center; border-radius: 6px;">
                                <div style="font-size: 8px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">{{ $kpi['label'] }}</div>
                                <div style="font-size: 15px; font-weight: 800; color: #711C37;">{{ $kpi['valor'] }}</div>
                            </td>
                            @if(!$loop->last)
                            <td style="width: 12px;"></td>
                            @endif
                            @endforeach
                        </tr>
                    </table>
                    @endif

                    {{-- Contenido dinámico inyectado --}}
                    @yield('tabla')

                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Sistema de Gestión de Inventario — Hotel Bugambilias</td>
                                <td style="text-align:right;font-weight:bold;color:#711C37;text-transform:uppercase;font-size:8px;">Confidencial</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
