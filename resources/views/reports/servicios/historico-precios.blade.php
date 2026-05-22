@extends('layouts.reporte-htb')

@section('content')
@php
    $paginas = $paginas ?? [];
    $totalPaginas = max(1, is_array($paginas) ? count($paginas) : 0);
    if (empty($paginas)) {
        $paginas = [[]];
    }
@endphp

@foreach ($paginas as $pIdx => $chunk)
<div class="report-page {{ $loop->last ? '' : 'page-break' }}">
    <table class="page-frame">
        <tbody>
            <tr><td class="frame-body">

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
                                <div class="hdr-title">{{ $nombreReporte ?? 'Histórico de Servicios por Precio por Moneda' }}</div>
                                <div class="hdr-code">Documento: {{ $codigoReporte ?? 'HTB-SER-001' }}</div>
                                <div class="hdr-sub">Sistema de Gestión Operativa · Hotel Bugambilias</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">Moneda</th>
                            <th style="width: 12%;">Código</th>
                            <th style="width: 28%;">Servicio</th>
                            <th style="width: 12%;">Precio</th>
                            <th style="width: 12%;">Vigencia Desde</th>
                            <th style="width: 12%;">Vigencia Hasta</th>
                            <th style="width: 10%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if (empty($chunk))
                        <tr>
                            <td colspan="7" style="text-align: center; color: #999; padding: 40px 0;">
                                No se encontraron registros de precios de servicios para los filtros seleccionados.
                            </td>
                        </tr>
                    @else
                        @foreach ($chunk as $fila)
                            @if ($fila['tipo'] === 'categoria')
                                <tr>
                                    <td colspan="7" class="grupo-hdr">
                                        <span class="grupo-name">{{ $fila['categoria'] }}</span>
                                    </td>
                                </tr>
                            @else
                                @php $item = $fila['item']; @endphp
                                <tr>
                                    <td>{{ $item->moneda_codigo ?: $item->moneda }}</td>
                                    <td style="font-family: monospace; font-size: 10px;">{{ $item->servicio_codigo }}</td>
                                    <td>{{ $item->servicio }}</td>
                                    <td style="text-align: right; font-weight: bold;">
                                        {{ number_format((float) $item->precio, 2) }}
                                    </td>
                                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</td>
                                    <td style="text-align: center;">
                                        {{ $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($item->es_oferta)
                                            <span style="color: #e67e22; font-weight: bold;">OFERTA</span>
                                        @elseif((int) $item->estado === 1)
                                            <span style="color: #27ae60; font-weight: bold;">Vigente</span>
                                        @else
                                            <span style="color: #e74c3c;">No Vigente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    </tbody>
                </table>

            </td></tr>
            <tr><td class="frame-footer">
                <div class="doc-footer">
                    <table>
                        <tr>
                            <td style="width:36%;">Emisión: {{ $fecha }}</td>
                            <td style="width:36%; text-align:center;">Responsable: <strong>{{ $usuario }}</strong></td>
                            <td style="width:28%; text-align:right;">
                                Página <strong>{{ $pIdx + 1 }}</strong> de <strong>{{ $totalPaginas }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </td></tr>
        </tbody>
    </table>
</div>
@endforeach
@endsection
