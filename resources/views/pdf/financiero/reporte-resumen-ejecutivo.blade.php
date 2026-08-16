@extends('reports.layout.app', [
    'nombreReporte' => $titulo,
    'codigoReporte' => $codigo,
])

@section('extra-css')
    .kpi-table { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 10px; }
    .kpi-card { border: 1px solid #e2e8f0; background: #f8fafc; padding: 14px; text-align: center; }
    .kpi-title { font-size: 8px; color: #4b5563; font-weight: bold; text-transform: uppercase; }
    .kpi-value { margin-top: 5px; font-size: 17px; color: #711C37; font-weight: bold; }
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

            <table class="kpi-table">
                <tr>
                    <td class="kpi-card" width="50%">
                        <div class="kpi-title">Total Bruto Reservado</div>
                        <div class="kpi-value">$ {{ number_format((float) $totalReservas, 2) }}</div>
                    </td>
                    <td class="kpi-card" width="50%">
                        <div class="kpi-title">Total Recaudado</div>
                        <div class="kpi-value">$ {{ number_format((float) $totalCobrado, 2) }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="kpi-card" width="50%">
                        <div class="kpi-title">Total Facturado Fiscalmente</div>
                        <div class="kpi-value">$ {{ number_format((float) $totalFacturado, 2) }}</div>
                    </td>
                    <td class="kpi-card" width="50%">
                        <div class="kpi-title">Numero de Reservaciones</div>
                        <div class="kpi-value">{{ $reservasCount }}</div>
                    </td>
                </tr>
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
