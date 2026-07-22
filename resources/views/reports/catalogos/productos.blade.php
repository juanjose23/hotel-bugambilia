@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('extra-css')
    .col-sku { width: 18%; }
    .col-nombre { width: 32%; }
    .col-desc { width: 50%; }
    .row-variant td { background: #f1f5f9 !important; font-size: 8px; }
    .row-variant td:first-child { padding-left: 12px; }
    .cell-muted { color: #94a3b8; font-size: 7px; }
    .badge-count { display: inline; font-size: 7px; color: #711C37; font-weight: bold; }
@endsection

@section('content')
    @foreach($paginas as $i => $items)
        <div class="pagina">
            <div class="report-header">
                @include('reports.layout.partials.header', [
                    'logo_base64' => $datosHotel['logo_base64'] ?? null,
                    'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
                ])
            </div>

            <div class="report-content">
                @if($i === 0 && !empty($filtrosResueltos))
                    <div class="filtros-info">
                        @foreach($filtrosResueltos as $j => $f)
                            <strong style="text-align:right;width:40%;border:none;padding:0;font-weight:bold;color:black;text-transform:uppercase;font-size:9px;">{{ $f['label'] }}:</strong> {{ $f['valor'] }}
                            @if($j < count($filtrosResueltos) - 1)
                                &nbsp;|&nbsp;
                            @endif
                        @endforeach
                    </div>
                @endif

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-sku">Código (SKU)</th>
                            <th class="col-nombre">Nombre</th>
                            <th class="col-desc">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($incluirVariantes)
                            @forelse($items as $producto)
                                <tr>
                                    <td class="sku-code">{{ $producto->codigo ?? '#' . $producto->id }}</td>
                                    <td>
                                        {{ $producto->nombre }}
                                        @if($producto->variantes->isNotEmpty())
                                            <span class="badge-count">({{ $producto->variantes->count() }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $producto->descripcion }}</td>
                                </tr>
                                @foreach($producto->variantes as $v)
                                    <tr class="row-variant">
                                        <td class="sku-code">{{ $v->codigo }}</td>
                                        <td>{{ $v->nombre_variante ?? 'Estándar' }}</td>
                                        <td>{{ $v->descripcion ?? '' }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;color:#718096;padding:12px;">
                                        No hay productos para mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($items as $producto)
                                <tr>
                                    <td class="sku-code">{{ $producto->codigo ?? '#' . $producto->id }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $producto->descripcion }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;color:#718096;padding:12px;">
                                        No hay productos para mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="report-footer">
                @include('reports.layout.partials.footer', [
                    'generadoEn' => $datosHotel['generadoEn'] ?? $datosHotel['fecha'] ?? now()->format('d/m/Y H:i'),
                    'usuario' => $datosHotel['usuario'] ?? 'Sistema',
                    'totalRegistros' => $totalRegistros,
                ])
            </div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection
