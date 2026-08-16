@extends('reports.layout.app', [
    'nombreReporte' => $titulo,
    'codigoReporte' => $codigo,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
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
                        <th>Estado</th>
                        <th>Codigo Reserva</th>
                        <th>Titular</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th class="amount">Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td><span class="badge badge-on">{{ $reserva->estado?->getLabel() ?? $reserva->estado }}</span></td>
                            <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                            <td>{{ $reserva->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $reserva->fecha_check_in?->format('d/m/Y') }}</td>
                            <td>{{ $reserva->fecha_check_out?->format('d/m/Y') }}</td>
                            <td class="amount">$ {{ number_format((float) $reserva->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">No hay datos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'usuario' => auth()->user()?->name ?? 'Sistema',
                'generadoEn' => now()->format('d/m/Y H:i'),
            ])
        </div>
    </div>
@endsection
