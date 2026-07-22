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
            <div style="margin-top:12px;margin-bottom:16px;background:#fff7ed;padding:12px 15px;border-radius:4px;border:1px solid #fed7aa;display:flex;gap:24px;align-items:center;">
                <div><span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Período:</span>&nbsp;<span style="font-size:11px;font-weight:bold;">{{ $fechaInicio }} — {{ $fechaFin }}</span></div>
                <div><span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Total Devoluciones:</span>&nbsp;<span style="font-size:13px;font-weight:bold;color:#dc2626;">{{ $totalDevoluciones }}</span></div>
            </div>

            <table class="data-table">
                <thead><tr>
                    <th>Código Dev.</th><th>Proveedor</th><th>OC Relacionada</th><th style="text-align:center;">Fecha</th><th>Estado</th><th>Motivo</th>
                </tr></thead>
                <tbody>
                    @forelse($items as $row)
                    <tr>
                        <td><strong>{{ $row->codigo }}</strong></td>
                        <td>{{ $row->proveedor_nombre }}</td>
                        <td style="font-size:9px;">{{ $row->orden_codigo }}</td>
                        <td style="text-align:center;">{{ $row->fecha_devolucion ? \Carbon\Carbon::parse($row->fecha_devolucion)->format('d/m/Y') : '—' }}</td>
                        <td style="font-size:9px;">{{ $row->estado }}</td>
                        <td style="font-size:9px;color:#555;">{{ $row->motivo ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;color:#888;padding:20px;">Sin devoluciones registradas en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => $generadoEn ?? $fecha ?? now()->format('d/m/Y H:i'),
                'usuario' => $usuario ?? 'TEST',
            ])
        </div>
    </div>
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
@endsection
