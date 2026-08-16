@extends('reports.layout.app', [
    'nombreReporte' => $titulo,
    'codigoReporte' => $codigo,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .total-box { margin-top: 12px; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc; text-align: right; font-size: 9px; }
    .total-box strong { color: #711C37; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>

        <div class="report-content">
            <div class="filtros-info">
                <strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>N. Factura</th>
                        <th>Cliente / Razon Social</th>
                        <th>Fecha Emision</th>
                        <th class="amount">Subtotal</th>
                        <th class="amount">Impuestos</th>
                        <th class="amount">Total General</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                        <tr>
                            <td><span class="sku-code">{{ $factura->numero ?? ('FAC-'.$factura->id) }}</span></td>
                            <td>{{ $factura->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $factura->fecha_emision?->format('d/m/Y') }}</td>
                            <td class="amount">$ {{ number_format((float) $factura->subtotal, 2) }}</td>
                            <td class="amount">$ {{ number_format((float) $factura->iva_total, 2) }}</td>
                            <td class="amount"><strong>$ {{ number_format((float) $factura->total, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">No se registraron facturas emitidas en este rango de fechas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="total-box">
                <div>Subtotal Acumulado: <strong>$ {{ number_format((float) $totalSubtotal, 2) }}</strong></div>
                <div>Impuestos Acumulados: <strong>$ {{ number_format((float) $totalImpuestos, 2) }}</strong></div>
                <div>Total Facturado: <strong>$ {{ number_format((float) $totalGeneral, 2) }}</strong></div>
            </div>
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'usuario' => auth()->user()?->name ?? 'Sistema',
                'generadoEn' => now()->format('d/m/Y H:i'),
            ])
        </div>
    </div>
@endsection
