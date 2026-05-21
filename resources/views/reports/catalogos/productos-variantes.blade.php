@extends('layouts.reporte-htb')

@section('content')

@foreach ($paginas as $pIdx => $chunk)
{{-- .page-break solo en páginas NO finales → evita página en blanco al final --}}
<div class="report-page {{ $loop->last ? '' : 'page-break' }}">

<table class="page-frame">
<tbody>
    <tr><td class="frame-body">

        {{-- ── Header corporativo ── --}}
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
                        <div class="hdr-title">{{ $nombreReporte }}</div>
                        <div class="hdr-code">Documento: {{ $codigoReporte }}</div>
                        <div class="hdr-sub">Sistema de Gestión Operativa · Hotel Bugambilias</div>
                    </td>
                </tr>
            </table>
        </div>

        @if (!$incluirVariantes)
        {{-- ══════════════════════════════════════════════════════════════════
             HTB-CP-001 · LISTA GENERAL DE PRODUCTOS
             $chunk = colección de objetos Producto
        ══════════════════════════════════════════════════════════════════ --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:52px; text-align:center;">Foto</th>
                    <th>Producto / Descripción</th>
                    <th style="width:115px;">Categoría</th>
                    <th style="width:100px;">Marca</th>
                    <th style="width:110px; text-align:center;">Tipo</th>
                    <th style="width:65px; text-align:center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chunk as $p)
                <tr>
                    <td style="text-align:center;">
                        @if(!empty($p->img_base64))
                            <img src="{{ $p->img_base64 }}" class="img-thumb">
                        @else
                            <span style="color:#ccc;font-size:8px;">S/F</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color:#711C37;">{{ $p->nombre }}</strong>
                        <div style="color:#64748b;font-size:8px;margin-top:2px;">{{ Str::limit($p->descripcion, 180) }}</div>
                    </td>
                    <td>{{ $p->categoria?->nombre ?? '—' }}</td>
                    <td>{{ $p->marca?->nombre ?? '—' }}</td>
                    <td style="text-align:center;">{{ $p->tipo_nombre }}</td>
                    <td style="text-align:center;">
                        <span class="badge {{ $p->estado ? 'badge-on' : 'badge-off' }}">
                            {{ $p->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @else
        {{-- ══════════════════════════════════════════════════════════════════
             HTB-CP-002 · CATÁLOGO DETALLADO – TABLA PLANA AGRUPADA
             $chunk = array de filas tipadas:
               ['tipo' => 'grupo',    'producto' => $p]
               ['tipo' => 'variante', 'producto' => $p, 'v' => $v|null]
             Todas las filas son <tr> de la misma tabla → altura uniforme
             y predecible, sin cards de altura variable.
        ══════════════════════════════════════════════════════════════════ --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:13%;">SKU</th>
                    <th style="width:22%;">Variante</th>
                    <th style="width:28%;">Especificaciones</th>
                    <th style="width:22%; text-align:center;">Código de Barras</th>
                    <th style="width:8%;  text-align:center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chunk as $fila)

                @if ($fila['tipo'] === 'grupo')
                {{-- Fila separadora de producto --}}
                <tr>
                    <td colspan="5" class="grupo-hdr">
                        <span class="grupo-name">{{ $fila['producto']->nombre }}</span>
                        <span class="grupo-meta">
                            {{ $fila['producto']->tipo_nombre }}
                            &nbsp;·&nbsp; Cat: {{ $fila['producto']->categoria?->nombre ?? 'N/A' }}
                            &nbsp;·&nbsp; Marca: {{ $fila['producto']->marca?->nombre ?? 'N/A' }}
                        </span>
                    </td>
                </tr>

                @else
                {{-- Fila de variante --}}
                <tr>
                    @if ($fila['v'])
                        <td class="sku-code">{{ $fila['v']->codigo }}</td>
                        <td>{{ $fila['v']->nombre_variante }}</td>
                        <td>
                            @php
                                $attrs = is_string($fila['v']->atributos)
                                    ? json_decode($fila['v']->atributos, true)
                                    : $fila['v']->atributos;
                            @endphp
                            @if($attrs && is_array($attrs))
                                @foreach($attrs as $k => $val)
                                    <div style="font-size:8px;"><strong>{{ strtoupper($k) }}:</strong> {{ $val }}</div>
                                @endforeach
                            @else —
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if(!empty($fila['v']->barcode_base64))
                                <img src="data:image/png;base64,{{ $fila['v']->barcode_base64 }}" class="bc-img-sm">
                                <div style="font-size:7px;color:#555;font-family:'Courier New';margin-top:1px;">{{ $fila['v']->codigo }}</div>
                            @else
                                <span style="color:#ccc;">N/A</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span class="badge {{ $fila['v']->estado ? 'badge-on' : 'badge-off' }}">
                                {{ $fila['v']->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                    @else
                        <td colspan="5" style="text-align:center;color:#94a3b8;padding:10px;">Sin variantes registradas</td>
                    @endif
                </tr>
                @endif

                @endforeach
            </tbody>
        </table>
        @endif

    </td></tr>

    {{-- Footer fijado al fondo de la página por la frame-table --}}
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
