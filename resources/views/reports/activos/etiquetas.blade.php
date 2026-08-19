@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@php
    $cols = $columnas ?? 3;
    $colWidth = round(100 / $cols, 2);
@endphp

@section('extra-css')
    .label-grid {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2mm;
        table-layout: fixed;
    }
    .label-grid td {
        width: {{ $colWidth }}%;
        padding: 3mm;
        vertical-align: top;
        border: none;
    }
    .label-grid td.empty-cell {
        border: none;
    }
    .label-card {
        display: block;
        width: 100%;
        height: 38mm;
        border: 1.5px solid #711C37;
        border-radius: 4px;
        background: #fff;
        overflow: hidden;
    }
    .label-card-header {
        background: #711C37;
        padding: 2.5mm 3mm 2mm;
    }
    .label-name {
        font-size: 9px;
        font-weight: bold;
        color: #fff;
        line-height: 1.3;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .label-variant {
        font-size: 7.5px;
        color: #f3c5d6;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-top: 1mm;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .label-card-body {
        padding: 2mm 3mm 2.5mm;
        text-align: center;
        background: #fff;
    }
    .label-barcode-img {
        display: block;
        width: 100%;
        height: auto;
        max-height: 18mm;
        margin: 0 auto 1.5mm;
        object-fit: contain;
    }
    .label-code-text {
        font-size: 8px;
        font-family: 'Courier New', monospace;
        color: #711C37;
        font-weight: bold;
        letter-spacing: 1px;
        background: #fdf2f6;
        border: 1px solid #e9c6d3;
        border-radius: 2px;
        padding: 1mm 2mm;
        display: inline-block;
        margin-top: 0;
    }
    .label-activo-badge {
        display: inline-block;
        font-size: 6px;
        color: #711C37;
        background: #fce7f3;
        border: 1px solid #f9a8d4;
        border-radius: 2px;
        padding: 0.3mm 1.5mm;
        margin-top: 0.5mm;
        font-weight: bold;
        text-transform: uppercase;
    }
    .empty-labels {
        text-align: center;
        color: #9ca3af;
        padding: 20mm 0;
        font-size: 11px;
    }
@endsection

@section('content')
    @foreach($paginas as $i => $labelsChunk)
        <div class="pagina">
            @if($i > 0)
                <div class="page-top-spacer"></div>
            @endif

            <div class="report-content">
                @if($i === 0 && !empty($filtrosResueltos))
                    <div class="filtros-box">
                        @foreach($filtrosResueltos as $j => $f)
                            <strong style="font-weight:bold;color:#711C37;text-transform:uppercase;font-size:8px;">{{ $f['label'] }}:</strong>
                            <span style="font-size:8px;color:#374151;">{{ $f['valor'] }}</span>
                            @if($j < count($filtrosResueltos) - 1)
                                &nbsp;<span style="color:#d1d5db;">|</span>&nbsp;
                            @endif
                        @endforeach
                    </div>
                @endif

                <table class="label-grid">
                    @php $cell = 0; $items = $labelsChunk->values(); @endphp

                    @forelse($items as $label)
                        @if($cell % $cols === 0)<tr>@endif

                        <td>
                            <div class="label-card">
                                <div class="label-card-header">
                                    <div class="label-name">{{ $label['producto'] }}</div>
                                    <div class="label-variant">{{ $label['variante'] }}</div>
                                </div>

                                <div class="label-card-body">
                                    <img
                                        src="{{ $label['barcode_base64'] }}"
                                        alt="{{ $label['codigo'] }}"
                                        class="label-barcode-img"
                                    >
                                    <div class="label-code-text">{{ $label['codigo'] }}</div>
                                </div>
                            </div>
                        </td>

                        @php $cell++; @endphp

                        @if($cell % $cols === 0 || $loop->last)
                            @php
                                $remaining = $cols - ($cell % $cols);
                                if ($loop->last && $remaining < $cols) {
                                    for ($r = 0; $r < $remaining; $r++) {
                                        echo '<td class="empty-cell"></td>';
                                    }
                                }
                            @endphp
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="{{ $cols }}" class="empty-labels">
                                No hay activos para generar etiquetas.
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection
