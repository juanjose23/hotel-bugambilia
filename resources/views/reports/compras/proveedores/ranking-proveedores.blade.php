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
            <div style="margin-top:10px ;margin-bottom:20px;background:#f8fafc;padding:12px 15px;border-radius:4px;border:1px solid #e2e8f0;">
                <span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Período:</span>&nbsp;<span style="font-size:11px;font-weight:bold;">{{ $fechaInicio }} — {{ $fechaFin }}</span>
            </div>

            <table class="data-table">
                <thead><tr>
                    <th style="text-align:center;">#</th>
                    <th>Proveedor</th>
                    <th style="text-align:center;">OCs Emitidas</th>
                    <th style="text-align:center;">OCs Recibidas</th>
                    <th style="text-align:center;">Lead Time Prom. (Días)</th>
                    <th style="text-align:center;">N° Devoluciones</th>
                    <th style="text-align:center;">% Devolución</th>
                    <th style="text-align:right;">Monto Comprado</th>
                </tr></thead>
                <tbody>
                    @forelse($items as $idx => $row)
                    @php
                        $leadColor = $row->promedio_dias_entrega <= 7 ? '#16a34a' : ($row->promedio_dias_entrega <= 14 ? '#d97706' : '#dc2626');
                        $devColor = $row->porcentaje_devoluciones > 10 ? '#dc2626' : ($row->porcentaje_devoluciones > 5 ? '#d97706' : '#16a34a');
                    @endphp
                    <tr>
                        <td style="text-align:center;font-weight:bold;font-size:15px;color:#711C37;">{{ $idx + 1 }}</td>
                        <td style=""><strong>{{ $row->proveedor_nombre }}</strong></td>
                        <td style="text-align:center;">{{ $row->total_ordenes }}</td>
                        <td style="text-align:center;">{{ $row->ordenes_recibidas }}</td>
                        <td style="text-align:center;font-weight:bold;color:{{ $leadColor }};">{{ $row->promedio_dias_entrega }} días</td>
                        <td style="text-align:center;">{{ $row->total_devoluciones }}</td>
                        <td style="text-align:center;font-weight:bold;color:{{ $devColor }};">{{ $row->porcentaje_devoluciones }}%</td>
                        <td style="text-align:right;font-weight:bold;color:#711C37;">${{ number_format((float)$row->monto_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;color:#888;padding:20px;">Sin proveedores con órdenes en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:12px;font-size:8px;">

    <span style="display:inline-block;padding:5px 10px;margin-right:6px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;border-radius:20px;">
        <x-heroicon-s-check-circle
            style="width:10px;height:10px;vertical-align:middle;margin-right:3px;" />
        <strong>Excelente</strong> < 7 días
    </span>

                <span style="display:inline-block;padding:5px 10px;margin-right:6px;background:#fef9c3;color:#854d0e;border:1px solid #fde68a;border-radius:20px;">
        <x-heroicon-s-exclamation-triangle
            style="width:10px;height:10px;vertical-align:middle;margin-right:3px;" />
        <strong>Aceptable</strong> 8–14 días
    </span>

                <span style="display:inline-block;padding:5px 10px;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;border-radius:20px;">
        <x-heroicon-s-x-circle
            style="width:10px;height:10px;vertical-align:middle;margin-right:3px;" />
        <strong>Crítico</strong> &gt; 14 días
    </span>

            </div>
        </div>
        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => now()->format('d/m/Y H:i'),
               'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
@endsection
