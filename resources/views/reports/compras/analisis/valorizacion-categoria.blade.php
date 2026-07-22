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
                &nbsp;&nbsp;<span style="font-size:9px;color:#711C37;font-weight:bold;text-transform:uppercase;">Total Invertido:</span>&nbsp;<span style="font-size:12px;font-weight:bold;color:#711C37;">${{ number_format($totalGeneral, 2) }}</span>
            </div>

            <table class="data-table">
                <thead><tr>
                    <th>Categoría</th><th style="text-align:center;">N° Órdenes</th><th style="text-align:right;">Total Invertido</th><th style="text-align:center;">% del Gasto</th><th style="width:30%;">Distribución</th>
                </tr></thead>
                <tbody>
                    @forelse($items as $row)
                    <tr>
                        <td><strong>{{ $row->categoria }}</strong></td>
                        <td style="text-align:center;">{{ $row->total_ordenes }}</td>
                        <td style="text-align:right;font-weight:bold;color:#711C37;">${{ number_format((float)$row->total_invertido, 2) }}</td>
                        <td style="text-align:center;font-weight:bold;">{{ $row->porcentaje }}%</td>
                        <td>
                            <div style="width:100%;background:#e2e8f0;height:10px;border-radius:5px;overflow:hidden;">
                                <div style="width:{{ min($row->porcentaje, 100) }}%;background:#711C37;height:100%;"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">Sin datos en el período.</td></tr>
                    @endforelse
                </tbody>
                @if(count($items) > 0)
                <tfoot><tr style="background:#f1f5f9;">
                    <td colspan="2" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:8px;">Total General:</td>
                    <td style="text-align:right;font-weight:bold;color:#711C37;font-size:14px;padding:8px;">${{ number_format($totalGeneral, 2) }}</td>
                    <td colspan="2"></td>
                </tr></tfoot>
                @endif
            </table>
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
