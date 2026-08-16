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
            <div class="filtros-info"><strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Huesped / Titular</th>
                        <th>Habitacion</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Noches</th>
                        <th>Estado</th>
                        <th class="amount">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                            <td>{{ $reserva->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $reserva->habitacion?->nombre ?? 'N/A' }}</td>
                            <td>{{ $reserva->fecha_check_in?->format('d/m/Y') }}</td>
                            <td>{{ $reserva->fecha_check_out?->format('d/m/Y') }}</td>
                            <td>{{ $reserva->noches }}</td>
                            <td><span class="badge badge-on">{{ $reserva->estado?->getLabel() ?? $reserva->estado }}</span></td>
                            <td class="amount">$ {{ number_format((float) $reserva->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-row">No se encontraron registros de reservacion en el rango seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="total-box">
                <div>Total Noches Reservadas: <strong>{{ $totalNoches }}</strong></div>
                <div>Monto Acumulado: <strong>$ {{ number_format((float) $totalIngresos, 2) }}</strong></div>
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
