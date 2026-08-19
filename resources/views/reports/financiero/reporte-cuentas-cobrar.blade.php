@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Reporte de Cuentas por Cobrar',
    'codigo' => $codigo ?? 'HTB-FIN-001',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('extra-css')
    .section-header { margin-top: 14px; margin-bottom: 6px; color: #711C37; font-size: 9.5pt; font-weight: bold; }
    .amount { text-align: right; white-space: nowrap; }
    .positive { color: #047857; font-weight: bold; }
    .danger { color: #b91c1c; font-weight: bold; }
    .total-box {
        margin-top: 12px;
        padding: 9px 10px;
        border: 1px solid #c5d0df;
        background: #f3f6fa;
        text-align: right;
        font-size: 9pt;
    }
    .total-box strong { color: #711C37; }
    .empty-row { text-align: center; color: #64748b; padding: 10px; font-size: 8pt; }
@endsection

@section('content')
    <div class="section-header">1. Reservaciones con Saldo Pendiente de Cobro</div>
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginasReservas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableView' => 'reports.financiero.tables.reporte-cuentas-cobrar-reservas',
    ])

    <div class="page-break"></div>

    <div class="section-header">2. Folios y Cuentas Abiertas con Saldo Pendiente</div>
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginasCuentas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableView' => 'reports.financiero.tables.reporte-cuentas-cobrar-cuentas',
    ])

    <div class="total-box">
        Monto Total Consolidado por Cobrar: <strong>$ {{ number_format((float) $totalPendiente, 2) }}</strong>
    </div>
@endsection
