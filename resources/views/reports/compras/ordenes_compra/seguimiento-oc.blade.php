@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
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
            <div style="margin-bottom:16px;background:#f8fafc;padding:12px 15px;border-radius:4px;border:1px solid #e2e8f0;">
                <span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Período:</span>&nbsp;<span style="font-size:11px;font-weight:bold;">{{ $fechaInicio }} — {{ $fechaFin }}</span>
            </div>

            <table class="data-table">
                <thead><tr>
                    <th>Código OC</th><th>Proveedor</th><th>Departamento</th><th style="text-align:center;">Fecha Orden</th><th style="text-align:center;">Entrega Est.</th><th style="text-align:center;">Estado</th><th style="text-align:center;">Recepciones</th><th style="text-align:right;">Total</th>
                </tr></thead>
                <tbody>
                    @forelse($items as $row)
                    @php $sinRecepcion = intval($row->total_recepciones) === 0; @endphp
                    <tr style="{{ $sinRecepcion ? 'background:#fff7ed;' : '' }}">
                        <td><strong>{{ $row->codigo }}</strong></td>
                        <td>{{ $row->proveedor_nombre }}</td>
                        <td style="font-size:9px;">{{ $row->departamento ?? '—' }}</td>
                        <td style="text-align:center;">{{ \Carbon\Carbon::parse($row->fecha_orden)->format('d/m/Y') }}</td>
                        <td style="text-align:center;{{ $row->fecha_entrega_estimada && \Carbon\Carbon::parse($row->fecha_entrega_estimada)->isPast() && $sinRecepcion ? 'color:#dc2626;font-weight:bold;' : '' }}">
                            {{ $row->fecha_entrega_estimada ? \Carbon\Carbon::parse($row->fecha_entrega_estimada)->format('d/m/Y') : '—' }}
                        </td>
                        <td style="text-align:center;font-size:9px;">{{ $row->estado }}</td>
                        <td style="text-align:center;font-weight:bold;color:{{ $sinRecepcion ? '#dc2626' : '#16a34a' }};">{{ $row->total_recepciones }}</td>
                        <td style="text-align:right;font-weight:bold;color:#711C37;">${{ number_format((float)$row->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;color:#888;padding:20px;">Sin órdenes en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:12px;font-size:8.5px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;padding:8px;border-radius:4px;">
                Las filas en naranja claro indican órdenes <strong>sin recepciones registradas</strong>. Las fechas de entrega en rojo están <strong>vencidas</strong>.
            </div>
        </div>
        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => now()->format('d/m/Y H:i'),
                'usuario' => 'Sistema',
            ])
        </div>
    </div>
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
@endsection
