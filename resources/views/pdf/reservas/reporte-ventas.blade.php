@extends('reports.layout.app', [
    'nombreReporte' => $titulo,
    'codigoReporte' => $codigo,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .positive { color: #047857; font-weight: bold; }
    .danger { color: #b91c1c; font-weight: bold; }
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
            <div class="filtros-info"><strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Codigo Reserva</th>
                        <th>Cliente</th>
                        <th>Canal Pago</th>
                        <th>Fecha Emision</th>
                        <th class="amount">Total Reserva</th>
                        <th class="amount">Total Pagado</th>
                        <th class="amount">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                            <td>{{ $reserva->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ strtoupper($reserva->canal_pago_reserva ?? $reserva->tipo_pago_reserva) }}</td>
                            <td>{{ $reserva->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="amount">$ {{ number_format((float) $reserva->total, 2) }}</td>
                            <td class="amount positive">$ {{ number_format((float) $reserva->total_pagado, 2) }}</td>
                            <td class="amount danger">$ {{ number_format((float) $reserva->saldo, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row">No hay registros de ventas en el periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="total-box">
                <div>Total Reservado: <strong>$ {{ number_format((float) $totalVentas, 2) }}</strong></div>
                <div>Total Recaudado: <strong>$ {{ number_format((float) $totalPagado, 2) }}</strong></div>
                <div>Saldo Pendiente: <strong>$ {{ number_format((float) $totalSaldo, 2) }}</strong></div>
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
