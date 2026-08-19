@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Resumen Ejecutivo de Rendimiento y Tomador de Decisiones',
    'codigo' => $codigo ?? 'HTB-FIN-003',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('extra-css')
    .summary-card { border: 1px solid #cbd5e1; border-radius: 4px; padding: 10px; margin-bottom: 12px; background: #ffffff; }
    .summary-card h3 { color: #711C37; font-size: 10pt; font-weight: bold; margin-bottom: 6px; border-b: 1px solid #f1f5f9; padding-bottom: 4px; }
    .metric-grid { width: 100%; border-collapse: collapse; }
    .metric-grid td { border: none; padding: 4px 6px; font-size: 8.5pt; }
    .metric-val { font-weight: bold; font-family: monospace; text-align: right; color: #0f172a; }
@endsection

@section('content')
    <div class="summary-card">
        <h3>1. Consolidado de Ingresos de Reservaciones</h3>
        <table class="metric-grid">
            <tr>
                <td>Ingresos Brutos por Reservas:</td>
                <td class="metric-val">$ {{ number_format((float) ($totalIngresosReservas ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Total Recaudado Efectivamente:</td>
                <td class="metric-val" style="color: #047857;">$ {{ number_format((float) ($totalRecaudado ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Cuentas por Cobrar Pendientes:</td>
                <td class="metric-val" style="color: #b91c1c;">$ {{ number_format((float) ($totalCuentasPorCobrar ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Cantidad de Reservaciones Atendidas:</td>
                <td class="metric-val">{{ $cantidadReservas ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-card">
        <h3>2. Resumen de Emisión Fiscal</h3>
        <table class="metric-grid">
            <tr>
                <td>Total Facturado Fiscalmente:</td>
                <td class="metric-val">$ {{ number_format((float) ($totalFacturadoFiscal ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>
@endsection
