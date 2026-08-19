@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Histórico de Servicios por Precio por Moneda',
    'codigo' => $codigoReporte ?? 'HTB-SER-001',
    'datosHotel' => $datosHotel ?? [],
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .badge-vigente { color: #047857; font-weight: bold; background: #d1fae5; padding: 2px 6px; border-radius: 4px; font-size: 7.5pt; }
    .badge-novigente { color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 7.5pt; }
    .cat-header { background-color: #f8fafc; font-weight: bold; color: #711C37; padding: 6px 8px; font-size: 8.5pt; border-left: 3px solid #711C37; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    <div class="report-content">
        @forelse($paginas as $i => $chunk)
            <div class="pagina">
                @if($i > 0)
                    <div class="page-top-spacer"></div>
                @endif

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Código</th>
                            <th style="width: 30%;">Servicio</th>
                            <th style="width: 15%;">Moneda</th>
                            <th class="amount" style="width: 15%;">Precio</th>
                            <th style="width: 15%; text-align: center;">Vigencia</th>
                            <th style="width: 10%; text-align: center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chunk as $row)
                            @if(($row['tipo'] ?? '') === 'categoria')
                                <tr>
                                    <td colspan="6" class="cat-header">
                                        Categoría: {{ $row['categoria'] ?? 'General' }}
                                    </td>
                                </tr>
                            @else
                                @php $item = (object) ($row['item'] ?? []); @endphp
                                <tr>
                                    <td><span class="sku-code">{{ $item->servicio_codigo ?? 'N/A' }}</span></td>
                                    <td><strong>{{ $item->servicio ?? 'N/A' }}</strong></td>
                                    <td>{{ $item->moneda ?? 'N/A' }} ({{ $item->moneda_simbolo ?? '$' }})</td>
                                    <td class="amount font-bold">{{ $item->moneda_simbolo ?? '$' }} {{ number_format((float) ($item->precio ?? 0), 2) }}</td>
                                    <td style="text-align: center; font-size: 7.5pt;">
                                        {{ $item->fecha_inicio ?? '—' }} al {{ $item->fecha_fin ?? 'Indefinido' }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if((int) ($item->estado ?? 1) === 1)
                                            <span class="badge-vigente">Vigente</span>
                                        @else
                                            <span class="badge-novigente">No Vigente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @empty
            <table class="data-table">
                <tbody>
                    <tr>
                        <td class="empty-row">No se encontraron registros de históricos de precios para los filtros seleccionados.</td>
                    </tr>
                </tbody>
            </table>
        @endforelse
    </div>
@endsection
