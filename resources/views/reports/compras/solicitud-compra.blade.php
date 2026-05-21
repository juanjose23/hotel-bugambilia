@extends('layouts.reporte-htb')

@section('report_code', $codigoReporte)
@section('report_name', $nombreReporte)

@section('content')
    @foreach ($paginas as $pIdx => $chunk)
        <div class="report-page {{ $loop->last ? '' : 'page-break' }}">
            <table class="page-frame">
                <tbody>
                    <tr>
                        <td class="frame-body">
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
                                            <div class="hdr-title">{{ $nombreReporte }}</div>
                                            <div class="hdr-code">{{ $codigoReporte }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="margin-bottom:16px;">
                                <table style="width:100%;border-collapse:collapse;">
                                    <tr>
                                        <td style="width:50%;vertical-align:top;padding-right:12px;">
                                            <strong style="font-size:11px;color:#711C37;">SOLICITUD DE COMPRA</strong><br>
                                            <span style="font-size:10px;">Código: <strong>{{ $solicitud->codigo }}</strong></span><br>
                                            <span style="font-size:10px;">Solicitante: {{ $solicitud->colaborador?->codigo }} - {{ $solicitud->colaborador?->persona?->primer_nombre }}</span><br>
                                            <span style="font-size:10px;">Departamento: {{ $solicitud->departamentoSolicitante?->nombre }}</span>
                                        </td>
                                        <td style="width:50%;vertical-align:top;text-align:right;">
                                            <span style="font-size:10px;">Fecha Solicitud: <strong>{{ $solicitud->fecha_solicitud?->format('d/m/Y') }}</strong></span><br>
                                            <span style="font-size:10px;">Fecha Necesita: <strong>{{ $solicitud->fecha_necesita?->format('d/m/Y') ?: 'No definida' }}</strong></span><br>
                                            <span style="font-size:10px;">Estado: <strong>{{ $estadoLabel }}</strong></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            @if ($solicitud->motivo)
                                <div style="margin-bottom:12px;">
                                    <strong style="font-size:10px;color:#711C37;">Motivo:</strong><br>
                                    <span style="font-size:10px;">{{ $solicitud->motivo }}</span>
                                </div>
                            @endif

                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width:40px;text-align:center;">#</th>
                                        <th>Producto</th>
                                        <th style="width:80px;text-align:center;">Variante</th>
                                        <th style="width:80px;text-align:center;">Cant. Solicitada</th>
                                        <th style="width:80px;text-align:center;">Cant. Aprobada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($chunk as $item)
                                        <tr>
                                            <td style="text-align:center;">{{ $loop->iteration }}</td>
                                            <td>{{ $item->producto?->nombre }}</td>
                                            <td style="text-align:center;">{{ $item->productoVariante?->codigo ?: '—' }}</td>
                                            <td style="text-align:center;">{{ number_format($item->cantidad_solicitada, 2) }}</td>
                                            <td style="text-align:center;">{{ $item->cantidad_aprobada !== null ? number_format($item->cantidad_aprobada, 2) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if ($solicitud->notes ?? $solicitud->notas ?? null)
                                <div style="margin-top:16px;padding:8px;border:1px solid #e2e8f0;background:#f8fafc;">
                                    <strong style="font-size:10px;color:#711C37;">Notas del área de compras:</strong><br>
                                    <span style="font-size:9px;">{{ $solicitud->notes ?? $solicitud->notas }}</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="frame-footer">
                            <div class="doc-footer">
                                <table>
                                    <tr>
                                        <td>Emisión: {{ $fecha }}</td>
                                        <td style="text-align:center;">Responsable: <strong>{{ $usuario }}</strong></td>
                                        <td style="text-align:right;">Página <strong>{{ $pIdx + 1 }}</strong> de <strong>{{ count($paginas) }}</strong></td>
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
