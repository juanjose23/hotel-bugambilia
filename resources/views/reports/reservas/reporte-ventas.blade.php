@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Ventas e Ingresos por Canal de Pago',
    'codigo' => $codigo ?? 'HTB-RES-002',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .positive { color: #047857; font-weight: bold; }
    .danger { color: #b91c1c; font-weight: bold; }
    .total-box { margin-top: 12px; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc; text-align: right; font-size: 9pt; }
    .total-box strong { color: #711C37; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableData' => [
            'totalVentas' => $totalVentas ?? 0,
            'totalPagado' => $totalPagado ?? 0,
            'totalSaldo' => $totalSaldo ?? 0,
        ],
        'tableView' => 'reports.reservas.tables.reporte-ventas',
    ])
@endsection
