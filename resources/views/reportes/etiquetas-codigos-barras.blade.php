@extends('layouts.reporte-htb')

@section('extra-css')
    .label-grid      { width: 100%; border-collapse: collapse; }
    .label-cell      { width: 33.33%; border: 1px dashed #cbd5e0; padding: 10px 6px; text-align: center; vertical-align: middle; }
    .label-row       { page-break-inside: avoid; }
    .lbl-prod { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #1a202c; display: block; margin-bottom: 2px; }
    .lbl-var  { font-size: 8px; color: #718096; display: block; margin-bottom: 6px; }
    .lbl-bc   { width: 100%; height: 44px; display: block; margin: 0 auto; }
    .lbl-sku  { font-size: 10px; font-weight: bold; color: #711C37; font-family: 'Courier New', monospace; margin-top: 4px; display: block; }
@endsection

@section('content')

@foreach ($paginas as $pIdx => $pagina)
{{-- ══ PÁGINA ══════════════════════════════════════════════════════════ --}}
{{-- .page-break solo en páginas NO finales → evita página en blanco al final --}}
<div class="report-page {{ $loop->last ? '' : 'page-break' }}">

<table class="page-frame">
<tbody>
    <tr><td class="frame-body">

        {{-- Header corporativo --}}
        <div class="doc-header">
            <table>
                <tr>
                    <td style="width: 35%;">
                        @if(!empty($logo_base64))
                            <img src="{{ $logo_base64 }}" class="hdr-logo">
                        @else
                            <strong style="font-size:17px;color:#711C37;">HOTEL BUGAMBILIAS</strong>
                        @endif
                    </td>
                    <td style="text-align:right; padding-right: 2px;">
                        <div class="hdr-title">{{ $nombreReporte }}</div>
                        <div class="hdr-code">Documento: {{ $codigoReporte }}</div>
                        <div class="hdr-sub">Sistema de Gestión Operativa · Hotel Bugambilias</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Cuadrícula de etiquetas --}}
        <table class="label-grid">
            @foreach ($pagina['filas'] as $fila)
            <tr class="label-row">
                @foreach ($fila as $etiqueta)
                <td class="label-cell">
                    <span class="lbl-prod">{{ $etiqueta['producto'] }}</span>
                    <span class="lbl-var">{{ $etiqueta['variante'] }}</span>
                    <img src="{{ $etiqueta['imagen'] }}" class="lbl-bc">
                    <span class="lbl-sku">{{ $etiqueta['codigo_barras'] }}</span>
                </td>
                @endforeach

                @if(count($fila) < 3)
                    @for ($i = count($fila); $i < 3; $i++)
                        <td style="width:33.33%; border:none;"></td>
                    @endfor
                @endif
            </tr>
            @endforeach
        </table>

    </td></tr>

    {{-- Footer fijado al fondo por la frame-table --}}
    <tr><td class="frame-footer">
        <div class="doc-footer">
            <table>
                <tr>
                    <td style="width:36%;">Emisión: {{ $fecha }}</td>
                    <td style="width:36%; text-align:center;">Responsable: <strong>{{ $usuario }}</strong></td>
                    <td style="width:28%; text-align:right;">
                        Página <strong>{{ $pIdx + 1 }}</strong> de <strong>{{ count($paginas) }}</strong>
                    </td>
                </tr>
            </table>
        </div>
    </td></tr>
</tbody>
</table>
</div>
{{-- /PÁGINA --}}
@endforeach

@endsection
