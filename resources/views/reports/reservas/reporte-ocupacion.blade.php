@use(App\Support\MonedaHelper)
@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Reporte de Ocupación y Estadías',
    'codigo' => $codigo ?? 'HTB-RES-001',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .total-box { margin-top: 12px; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc; text-align: right; font-size: 9pt; }
    .total-box strong { color: #711C37; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
    .occ-cards { margin-bottom: 12px; }
    .occ-card { display: inline-block; vertical-align: top; width: 18%; margin-right: 1%; border: 1px solid #e2e8f0; border-top: 3px solid #711C37; background: #fff; padding: 8px 10px; border-radius: 4px; }
    .occ-label { font-size: 6.5pt; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
    .occ-value { font-size: 13pt; font-weight: 700; color: #0f172a; }
    .occ-value small { font-size: 8pt; color: #711C37; }
    .occ-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 14px; }
    .occ-table th, .occ-table td { border: 1px solid #e2e8f0; padding: 4px 6px; text-align: center; }
    .occ-table th { background: #f1f5f9; text-transform: uppercase; font-size: 6.5pt; color: #475569; }
@endsection

@section('content')
    @if(isset($ocupacionPorDia) && count($ocupacionPorDia) > 0)
        <div class="occ-cards">
            <div class="occ-card">
                <div class="occ-label">Total Habitaciones</div>
                <div class="occ-value">{{ $totalHabitaciones ?? 0 }}</div>
            </div>
            <div class="occ-card">
                <div class="occ-label">Ocupación Promedio</div>
                <div class="occ-value">{{ number_format($porcentajePromedio ?? 0, 1) }}<small>%</small></div>
            </div>
            <div class="occ-card">
                <div class="occ-label">Noches Reservadas</div>
                <div class="occ-value">{{ $totalNoches ?? 0 }}</div>
            </div>
            <div class="occ-card">
                <div class="occ-label">Total Ingresos</div>
                <div class="occ-value">{{ MonedaHelper::simbolo() }} {{ number_format($totalIngresos ?? 0, 2) }}</div>
            </div>
            <div class="occ-card">
                <div class="occ-label">ADR</div>
                <div class="occ-value">{{ MonedaHelper::simbolo() }} {{ number_format($adr ?? 0, 2) }}</div>
            </div>
        </div>

        <table class="occ-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Habitaciones Ocupadas</th>
                    <th>Total Habitaciones</th>
                    <th>Ocupación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ocupacionPorDia as $fecha => $data)
                    <tr>
                        <td>{{ \Carbon\CarbonImmutable::parse($fecha)->format('d/m/Y') }}</td>
                        <td>{{ $data['ocupadas'] }}</td>
                        <td>{{ $data['total'] }}</td>
                        <td>{{ number_format((float) ($data['porcentaje'] ?? 0), 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableData' => [
            'totalNoches' => $totalNoches ?? 0,
            'totalIngresos' => $totalIngresos ?? 0,
        ],
        'tableView' => 'reports.reservas.tables.reporte-ocupacion',
    ])
@endsection
